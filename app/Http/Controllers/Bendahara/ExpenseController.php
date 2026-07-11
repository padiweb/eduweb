<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Expense;
use App\Models\ExpenseApproval;
use App\Models\ExpenseCategory;
use App\Models\FundSource;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    private function school() { return auth()->user()->school; }

    // ── Kategori ──────────────────────────────────────────────────────────────

    public function categories()
    {
        $school = $this->school();
        $categories = ExpenseCategory::forSchool($school->id)
            ->withCount('expenses')
            ->orderBy('type')->orderBy('name')
            ->get();

        return view('bendahara.expenses.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'code'               => 'nullable|string|max:20',
            'type'               => 'required|in:payroll,activity,operational,other',
            'requires_approval'  => 'boolean',
            'approval_threshold' => 'nullable|integer|min:0',
        ]);

        $school = $this->school();
        $data['school_id']          = $school->id;
        $data['is_active']          = true;
        $data['requires_approval']  = $request->boolean('requires_approval');
        $data['approval_threshold'] = $data['approval_threshold'] ?? 0;

        ExpenseCategory::create($data);

        return back()->with('success', "Kategori \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function updateCategory(Request $request, ExpenseCategory $category)
    {
        if ($category->school_id !== $this->school()->id) abort(403);

        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'code'               => 'nullable|string|max:20',
            'type'               => 'required|in:payroll,activity,operational,other',
            'requires_approval'  => 'boolean',
            'approval_threshold' => 'nullable|integer|min:0',
            'is_active'          => 'boolean',
        ]);
        $data['requires_approval']  = $request->boolean('requires_approval');
        $data['approval_threshold'] = $data['approval_threshold'] ?? 0;

        $category->update($data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    // ── Pengeluaran ───────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $school = $this->school();

        $query = Expense::forSchool($school->id)
            ->with(['fundSource', 'category', 'createdBy', 'approvedBy', 'academicYear'])
            ->orderByDesc('expense_date');

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('source'))   $query->where('fund_source_id', $request->source);
        if ($request->filled('category')) $query->where('expense_category_id', $request->category);
        if ($request->filled('year'))     $query->where('academic_year_id', $request->year);
        if ($request->filled('search'))   $query->where('description', 'like', "%{$request->search}%");

        $expenses      = $query->paginate(25)->withQueryString();
        $sources       = FundSource::forSchool($school->id)->active()->get();
        $categories    = ExpenseCategory::forSchool($school->id)->active()->get();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderByDesc('is_active')->get();

        // Summary
        $totalApproved = Expense::forSchool($school->id)->approved()
            ->when($request->year, fn($q) => $q->where('academic_year_id', $request->year))
            ->sum('amount');
        $totalPending = Expense::forSchool($school->id)->pending()->count();

        return view('bendahara.expenses.index', compact(
            'expenses', 'sources', 'categories', 'academicYears',
            'totalApproved', 'totalPending'
        ));
    }

    public function create()
    {
        $school = $this->school();
        $sources       = FundSource::forSchool($school->id)->active()->get();
        $categories    = ExpenseCategory::forSchool($school->id)->active()->get();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderByDesc('is_active')->get();

        return view('bendahara.expenses.create', compact('sources', 'categories', 'academicYears'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fund_source_id'       => 'required|exists:fund_sources,id',
            'expense_category_id'  => 'required|exists:expense_categories,id',
            'academic_year_id'     => 'required|exists:academic_years,id',
            'description'          => 'required|string|max:255',
            'amount'               => 'required|integer|min:1',
            'expense_date'         => 'required|date',
            'period_label'         => 'nullable|string|max:50',
            'reference_number'     => 'nullable|string|max:50',
            'notes'                => 'nullable|string|max:1000',
            'attachment'           => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $school   = $this->school();
        $category = ExpenseCategory::findOrFail($data['expense_category_id']);

        if ($category->school_id !== $school->id) abort(403);

        // Cek apakah butuh approval
        $needsApproval = $category->needsApproval((int) $data['amount']);
        $status        = $needsApproval ? 'pending_approval' : 'approved';

        // Kalau tidak butuh approval, langsung approved oleh bendahara
        if (!$needsApproval) {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
        }

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')
                ->store('finance/expenses/' . $school->id, 'private');
        }

        $expense = Expense::create([
            'school_id'           => $school->id,
            'fund_source_id'      => $data['fund_source_id'],
            'expense_category_id' => $data['expense_category_id'],
            'academic_year_id'    => $data['academic_year_id'],
            'description'         => $data['description'],
            'amount'              => $data['amount'],
            'expense_date'        => $data['expense_date'],
            'period_label'        => $data['period_label'] ?? null,
            'reference_number'    => $data['reference_number'] ?? null,
            'notes'               => $data['notes'] ?? null,
            'attachment_path'     => $data['attachment_path'] ?? null,
            'status'              => $status,
            'approved_by'         => $data['approved_by'] ?? null,
            'approved_at'         => $data['approved_at'] ?? null,
            'created_by'          => auth()->id(),
        ]);

        // Log approval
        ExpenseApproval::create([
            'expense_id' => $expense->id,
            'school_id'  => $school->id,
            'user_id'    => auth()->id(),
            'action'     => $needsApproval ? 'submitted' : 'approved',
            'notes'      => $needsApproval ? 'Diajukan untuk approval kepala sekolah' : 'Auto-approved (di bawah batas nominal)',
            'ip_address' => $request->ip(),
        ]);

        $msg = $needsApproval
            ? 'Pengeluaran diajukan dan menunggu approval kepala sekolah.'
            : 'Pengeluaran berhasil dicatat.';

        return redirect()->route('bendahara.expenses.index')->with('success', $msg);
    }

    public function show(Expense $expense)
    {
        $this->authorize($expense);
        $expense->load(['fundSource', 'category', 'createdBy', 'approvedBy', 'academicYear', 'approvals.user']);
        return view('bendahara.expenses.show', compact('expense'));
    }

    // ── Approval (Kepala Sekolah) ─────────────────────────────────────────────

    public function pendingApprovals()
    {
        $school = $this->school();
        $expenses = Expense::forSchool($school->id)
            ->pending()
            ->with(['fundSource', 'category', 'createdBy', 'academicYear'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('bendahara.expenses.pending', compact('expenses'));
    }

    public function approve(Request $request, Expense $expense)
    {
        $this->authorize($expense);
        $this->requireKepala();

        if ($expense->status !== 'pending_approval') {
            return back()->withErrors(['action' => 'Hanya pengeluaran pending yang bisa disetujui.']);
        }

        $expense->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        ExpenseApproval::create([
            'expense_id' => $expense->id,
            'school_id'  => $this->school()->id,
            'user_id'    => auth()->id(),
            'action'     => 'approved',
            'notes'      => $request->notes,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Pengeluaran berhasil disetujui.');
    }

    public function reject(Request $request, Expense $expense)
    {
        $this->authorize($expense);
        $this->requireKepala();

        $request->validate(['rejection_reason' => 'required|string|max:255']);

        if ($expense->status !== 'pending_approval') {
            return back()->withErrors(['action' => 'Hanya pengeluaran pending yang bisa ditolak.']);
        }

        $expense->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        ExpenseApproval::create([
            'expense_id' => $expense->id,
            'school_id'  => $this->school()->id,
            'user_id'    => auth()->id(),
            'action'     => 'rejected',
            'notes'      => $request->rejection_reason,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Pengeluaran ditolak.');
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function authorize(Expense $expense)
    {
        if ($expense->school_id !== $this->school()->id) abort(403);
    }

    private function requireKepala()
    {
        if (!in_array(auth()->user()->role, ['kepala_sekolah', 'bendahara'])) {
            abort(403, 'Hanya kepala sekolah yang dapat menyetujui pengeluaran.');
        }
    }
}
