<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PaymentBill;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user();

        $query = PaymentBill::where('user_id', $student->id)
            ->with(['paymentType', 'academicYear', 'installments', 'transactions'])
            ->orderByDesc('period_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bills = $query->paginate(15)->withQueryString();

        // amount_remaining adalah accessor (bukan kolom DB), tidak bisa di-sum langsung
        // Hitung manual: sum(amount_billed) - sum(amount_paid) untuk status unpaid/partial
        $totalTagihan = PaymentBill::where('user_id', $student->id)->sum('amount_billed');
        $totalBayar   = PaymentBill::where('user_id', $student->id)->sum('amount_paid');

        $tunggakanRows = PaymentBill::where('user_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->selectRaw('SUM(amount_billed - amount_paid) as sisa')
            ->value('sisa');

        $summary = [
            'total_tagihan' => (int) $totalTagihan,
            'total_bayar'   => (int) $totalBayar,
            'tunggakan'     => (int) ($tunggakanRows ?? 0),
            'lunas'         => PaymentBill::where('user_id', $student->id)->where('status', 'paid')->count(),
        ];

        return view('siswa.payment.index', compact('bills', 'summary'));
    }

    public function show(PaymentBill $bill)
    {
        if ($bill->user_id !== auth()->id()) abort(403);

        $bill->load(['paymentType', 'academicYear', 'installments', 'transactions.confirmedBy']);

        $school = auth()->user()->school;

        return view('siswa.payment.show', compact('bill', 'school'));
    }

    public function uploadReceipt(Request $request, PaymentBill $bill)
    {
        if ($bill->user_id !== auth()->id()) abort(403);

        $request->validate([
            'amount'         => 'required|integer|min:1',
            'installment_id' => 'nullable|exists:payment_installments,id',
            'bank_name'      => 'required|string|max:100',
            'sender_name'    => 'required|string|max:150',
            'transfer_date'  => 'required|date|before_or_equal:today',
            'receipt'        => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes'          => 'nullable|string|max:500',
        ]);

        if (in_array($bill->status, ['paid', 'waived'])) {
            return back()->withErrors(['receipt' => 'Tagihan ini sudah lunas atau dibebaskan.']);
        }

        $hasPending = $bill->transactions()->where('status', 'pending')->exists();
        if ($hasPending) {
            return back()->withErrors(['receipt' => 'Masih ada pembayaran yang menunggu konfirmasi. Tunggu sebelum upload lagi.']);
        }

        $path = $request->file('receipt')->store(
            'payment-receipts/' . $bill->school_id,
            'private'
        );

        PaymentTransaction::create([
            'school_id'              => $bill->school_id,
            'payment_bill_id'        => $bill->id,
            'payment_installment_id' => $request->installment_id ?? null,
            'user_id'                => $bill->user_id,
            'reference_number'       => 'TRF-' . strtoupper(Str::random(10)),
            'amount'                 => (int) $request->amount,
            'channel'                => 'transfer',
            'status'                 => 'pending',
            'receipt_path'           => $path,
            'bank_name'              => $request->bank_name,
            'sender_name'            => $request->sender_name,
            'transfer_date'          => $request->transfer_date,
            'notes'                  => $request->notes,
            'created_by'             => auth()->id(),
            'created_by_ip'          => $request->ip(),
        ]);

        return back()->with('success', 'Bukti transfer berhasil dikirim. Menunggu konfirmasi bendahara.');
    }
}
