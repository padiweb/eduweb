<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FundIncome;
use App\Models\FundSource;
use Illuminate\Http\Request;

class FundSourceController extends Controller
{
    private function school() { return auth()->user()->school; }

    public function index()
    {
        $school  = $this->school();
        $sources = FundSource::forSchool($school->id)
            ->withCount(['incomes', 'expenses'])
            ->orderBy('type')->orderBy('name')
            ->get();

        return view('bendahara.fund-sources.index', compact('sources'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:20',
            'type'        => 'required|in:siswa,bos,bosda,other',
            'description' => 'nullable|string|max:500',
        ]);

        $school            = $this->school();
        $data['school_id'] = $school->id;
        $data['is_active'] = true;

        FundSource::create($data);

        return back()->with('success', "Sumber dana \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, FundSource $fundSource)
    {
        $this->authorize($fundSource);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:20',
            'type'        => 'required|in:siswa,bos,bosda,other',
            'description' => 'nullable|string|max:500',
        ]);

        $fundSource->update($data);

        return back()->with('success', 'Sumber dana berhasil diperbarui.');
    }

    // Nonaktifkan/aktifkan sumber dana — tidak bisa dihapus agar history tetap ada
    public function toggleActive(Request $request, FundSource $fundSource)
    {
        $this->authorize($fundSource);

        $fundSource->update(['is_active' => ! $fundSource->is_active]);
        $label = $fundSource->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Sumber dana \"{$fundSource->name}\" {$label}.");
    }

    // ── Pemasukan Dana ────────────────────────────────────────────────────────

    public function incomes(FundSource $fundSource)
    {
        $this->authorize($fundSource);
        $school = $this->school();

        $fundSource->load(['incomes' => fn($q) =>
            $q->with(['academicYear', 'createdBy'])->orderByDesc('income_date')
        ]);
        $academicYears = AcademicYear::where('school_id', $school->id)
            ->orderByDesc('is_active')->get();
        $totalIncome = $fundSource->incomes->sum('amount');

        return view('bendahara.fund-sources.incomes', compact('fundSource', 'academicYears', 'totalIncome'));
    }

    public function storeIncome(Request $request, FundSource $fundSource)
    {
        $this->authorize($fundSource);

        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'description'      => 'required|string|max:255',
            'amount'           => 'required|integer|min:1',
            'income_date'      => 'required|date',
            'period_label'     => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:50',
            'notes'            => 'nullable|string|max:500',
            'attachment'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $school = $this->school();

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')
                ->store('finance/incomes/' . $school->id, 'private');
        }

        FundIncome::create([
            'school_id'        => $school->id,
            'fund_source_id'   => $fundSource->id,
            'academic_year_id' => $data['academic_year_id'],
            'description'      => $data['description'],
            'amount'           => $data['amount'],
            'income_date'      => $data['income_date'],
            'period_label'     => $data['period_label'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'attachment_path'  => $data['attachment_path'] ?? null,
            'created_by'       => auth()->id(),
        ]);

        return back()->with('success', 'Pemasukan dana berhasil dicatat.');
    }

    // Edit pemasukan
    public function updateIncome(Request $request, FundIncome $income)
    {
        if ($income->school_id !== $this->school()->id) abort(403);

        $data = $request->validate([
            'description'      => 'required|string|max:255',
            'amount'           => 'required|integer|min:1',
            'income_date'      => 'required|date',
            'period_label'     => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:50',
            'notes'            => 'nullable|string|max:500',
        ]);

        $income->update($data);

        return back()->with('success', 'Data pemasukan berhasil diperbarui.');
    }

    // Hapus pemasukan
    public function destroyIncome(FundIncome $income)
    {
        if ($income->school_id !== $this->school()->id) abort(403);
        $income->delete();
        return back()->with('success', 'Data pemasukan berhasil dihapus.');
    }

    private function authorize(FundSource $model)
    {
        if ($model->school_id !== $this->school()->id) abort(403);
    }
}
