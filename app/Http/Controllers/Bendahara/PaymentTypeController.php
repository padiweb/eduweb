<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\PaymentAuditLog;
use App\Models\PaymentRate;
use App\Models\PaymentType;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{
    private function school()
    {
        return auth()->user()->school;
    }

    public function index()
    {
        $school = $this->school();

        $types = PaymentType::where('school_id', $school->id)
            ->withCount('bills')
            ->with(['rates' => fn($q) => $q->with(['classroom', 'major', 'academicYear'])])
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::where('school_id', $school->id)
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        $classrooms = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->with('major')
            ->orderBy('name')
            ->get();

        $majors = Major::where('school_id', $school->id)->orderBy('name')->get();

        return view('bendahara.payment-types.index', compact(
            'types', 'academicYears', 'classrooms', 'majors'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:20',
            'category'    => 'required|in:spp,ujian,kegiatan,seragam,lainnya',
            'period_type' => 'required|in:monthly,semester,once',
            'description' => 'nullable|string|max:500',
        ]);

        $school = $this->school();
        $data['school_id'] = $school->id;
        $data['is_active'] = true;

        $type = PaymentType::create($data);

        PaymentAuditLog::create([
            'school_id'   => $school->id,
            'user_id'     => auth()->id(),
            'action'      => 'payment_type_created',
            'target_type' => 'PaymentType',
            'target_id'   => $type->id,
            'new_values'  => $data,
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', "Jenis pembayaran \"{$type->name}\" berhasil ditambahkan.");
    }

    public function update(Request $request, PaymentType $paymentType)
    {
        $this->authorize($paymentType);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:20',
            'category'    => 'required|in:spp,ujian,kegiatan,seragam,lainnya',
            'period_type' => 'required|in:monthly,semester,once',
            'description' => 'nullable|string|max:500',
        ]);

        $old = $paymentType->toArray();
        $paymentType->update($data);

        PaymentAuditLog::create([
            'school_id'   => $this->school()->id,
            'user_id'     => auth()->id(),
            'action'      => 'payment_type_updated',
            'target_type' => 'PaymentType',
            'target_id'   => $paymentType->id,
            'old_values'  => $old,
            'new_values'  => $data,
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'Jenis pembayaran berhasil diperbarui.');
    }

    public function toggleActive(Request $request, PaymentType $paymentType)
    {
        $this->authorize($paymentType);

        $paymentType->update(['is_active' => !$paymentType->is_active]);
        $label = $paymentType->is_active ? 'diaktifkan' : 'dinonaktifkan';

        PaymentAuditLog::create([
            'school_id'   => $this->school()->id,
            'user_id'     => auth()->id(),
            'action'      => 'payment_type_toggled',
            'target_type' => 'PaymentType',
            'target_id'   => $paymentType->id,
            'new_values'  => ['is_active' => $paymentType->is_active],
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', "\"{$paymentType->name}\" {$label}.");
    }

    public function storeRate(Request $request, PaymentType $paymentType)
    {
        $this->authorize($paymentType);

        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'classroom_id'     => 'nullable|exists:classrooms,id',
            'major_id'         => 'nullable|exists:majors,id',
            'amount'           => 'required|integer|min:0',
        ]);

        $school = $this->school();

        $exists = PaymentRate::where('payment_type_id', $paymentType->id)
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('classroom_id', $data['classroom_id'] ?? null)
            ->where('major_id', $data['major_id'] ?? null)
            ->exists();

        if ($exists) {
            return back()->withErrors(['amount' => 'Tarif untuk kombinasi ini sudah ada.']);
        }

        $rate = PaymentRate::create([
            'payment_type_id'  => $paymentType->id,
            'school_id'        => $school->id,
            'academic_year_id' => $data['academic_year_id'],
            'classroom_id'     => $data['classroom_id'] ?? null,
            'major_id'         => $data['major_id'] ?? null,
            'amount'           => $data['amount'],
            'is_active'        => true,
        ]);

        PaymentAuditLog::create([
            'school_id'   => $school->id,
            'user_id'     => auth()->id(),
            'action'      => 'payment_rate_created',
            'target_type' => 'PaymentRate',
            'target_id'   => $rate->id,
            'new_values'  => $data,
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'Tarif berhasil ditambahkan.');
    }

    public function updateRate(Request $request, PaymentRate $rate)
    {
        $this->authorize($rate->paymentType);
        $request->validate(['amount' => 'required|integer|min:0']);
        $old = $rate->amount;
        $rate->update(['amount' => (int) $request->amount]);
        PaymentAuditLog::create([
            'school_id'   => $this->school()->id,
            'user_id'     => auth()->id(),
            'action'      => 'payment_rate_updated',
            'target_type' => 'PaymentRate',
            'target_id'   => $rate->id,
            'old_values'  => ['amount' => $old],
            'new_values'  => ['amount' => $rate->amount],
            'ip_address'  => $request->ip(),
        ]);
        return back()->with('success', 'Nominal tarif berhasil diperbarui.');
    }

    public function destroyRate(Request $request, PaymentRate $rate)
    {
        $this->authorize($rate->paymentType);

        // Boleh hapus jika belum ada tagihan ATAU amount = 0 (belum diisi)
        if ($rate->bills()->count() > 0 && $rate->amount > 0) {
            return back()->withErrors(['rate' => 'Tarif tidak dapat dihapus karena sudah digunakan pada tagihan.']);
        }

        PaymentAuditLog::create([
            'school_id'   => $this->school()->id,
            'user_id'     => auth()->id(),
            'action'      => 'payment_rate_deleted',
            'target_type' => 'PaymentRate',
            'target_id'   => $rate->id,
            'old_values'  => $rate->toArray(),
            'ip_address'  => $request->ip(),
        ]);

        $rate->delete();

        return back()->with('success', 'Tarif berhasil dihapus.');
    }

    private function authorize(PaymentType $model)
    {
        if ($model->school_id !== $this->school()->id) {
            abort(403);
        }
    }
}