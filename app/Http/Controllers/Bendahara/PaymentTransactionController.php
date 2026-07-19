<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\PaymentAuditLog;
use App\Models\PaymentInstallment;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentTransactionController extends Controller
{
    private function school()
    {
        return auth()->user()->school;
    }

    public function index(Request $request)
    {
        $school = $this->school();

        $status = $request->get('status', 'pending');

        $transactions = PaymentTransaction::where('school_id', $school->id)
            ->where('status', $status)
            ->with(['bill.student', 'bill.paymentType', 'createdBy'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('bendahara.transactions.index', compact('transactions', 'status'));
    }

    public function approve(Request $request, PaymentTransaction $transaction)
    {
        $this->authorize($transaction);

        if ($transaction->status !== 'pending') {
            return back()->withErrors(['action' => 'Hanya transaksi pending yang dapat dikonfirmasi.']);
        }

        $transaction->update([
            'status'       => 'approved',
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        if ($transaction->payment_installment_id) {
            $inst = PaymentInstallment::find($transaction->payment_installment_id);
            if ($inst) {
                $inst->amount_paid += $transaction->amount;
                $inst->status = $inst->amount_paid >= $inst->amount_due ? 'paid' : 'partial';
                $inst->save();
            }
        }

        $transaction->bill->recalculateStatus();

        PaymentAuditLog::create([
            'school_id'   => $this->school()->id,
            'user_id'     => auth()->id(),
            'action'      => 'transfer_approved',
            'target_type' => 'PaymentTransaction',
            'target_id'   => $transaction->id,
            'new_values'  => ['status' => 'approved'],
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'Pembayaran Rp ' . number_format($transaction->amount, 0, ',', '.') . ' berhasil dikonfirmasi.');
    }

    public function reject(Request $request, PaymentTransaction $transaction)
    {
        $this->authorize($transaction);

        $request->validate(['rejection_reason' => 'required|string|max:255']);

        if ($transaction->status !== 'pending') {
            return back()->withErrors(['action' => 'Hanya transaksi pending yang dapat ditolak.']);
        }

        $transaction->update([
            'status'           => 'rejected',
            'confirmed_by'     => auth()->id(),
            'confirmed_at'     => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        PaymentAuditLog::create([
            'school_id'   => $this->school()->id,
            'user_id'     => auth()->id(),
            'action'      => 'transfer_rejected',
            'target_type' => 'PaymentTransaction',
            'target_id'   => $transaction->id,
            'new_values'  => ['status' => 'rejected', 'reason' => $request->rejection_reason],
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'Transaksi ditolak.');
    }

    public function viewReceipt(PaymentTransaction $transaction)
    {
        $user      = auth()->user();
        $isOwner   = $transaction->user_id === $user->id;
        $isFinance = in_array($user->role, ['bendahara', 'kepala_sekolah']);

        if (!$isOwner && !$isFinance) abort(403);
        $this->authorize($transaction);

        if (!$transaction->receipt_path) {
            abort(404, 'Tidak ada bukti transfer.');
        }

        // Coba via disk 'private' dulu (cara yang benar)
        if (Storage::disk('private')->exists($transaction->receipt_path)) {
            $fullPath = Storage::disk('private')->path($transaction->receipt_path);
            return response()->file($fullPath);
        }

        // Fallback: coba tanpa prefix (path lama mungkin sudah include 'payment-receipts/...')
        $fallbackPaths = [
            storage_path('app/private/' . $transaction->receipt_path),
            storage_path('app/' . $transaction->receipt_path),
        ];
        foreach ($fallbackPaths as $path) {
            if (file_exists($path)) {
                return response()->file($path);
            }
        }

        abort(404, 'File bukti transfer tidak ditemukan di server. Path: ' . $transaction->receipt_path);
    }

    private function authorize(PaymentTransaction $transaction)
    {
        if ($transaction->school_id !== $this->school()->id) abort(403);
    }
}