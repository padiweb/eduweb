<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FundIncome;
use App\Models\FundSource;
use App\Models\Payroll;
use Illuminate\Http\Request;

class FinanceDashboardController extends Controller
{
    private function school() { return auth()->user()->school; }

    public function index()
    {
        $school = $this->school();
        $yearId = AcademicYear::where('school_id', $school->id)
            ->where('is_active', true)->value('id');

        // Sumber dana dengan saldo masing-masing
        $sources = FundSource::forSchool($school->id)
            ->active()
            ->get()
            ->map(function ($s) use ($yearId) {
                $income  = FundIncome::where('fund_source_id', $s->id)
                    ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
                    ->sum('amount');
                $expense = Expense::where('fund_source_id', $s->id)
                    ->where('status', 'approved')
                    ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
                    ->sum('amount');
                $s->income_total  = (int) $income;
                $s->expense_total = (int) $expense;
                $s->balance       = (int) $income - (int) $expense;
                return $s;
            });

        $totalIncome  = $sources->sum('income_total');
        $totalExpense = $sources->sum('expense_total');
        $totalBalance = $totalIncome - $totalExpense;

        // Pengeluaran menunggu approval
        $pendingExpenses = Expense::forSchool($school->id)
            ->pending()
            ->with(['category', 'fundSource', 'createdBy'])
            ->latest()
            ->take(5)
            ->get();

        // Pengeluaran terbaru
        $recentExpenses = Expense::forSchool($school->id)
            ->approved()
            ->with(['category', 'fundSource'])
            ->orderByDesc('expense_date')
            ->take(5)
            ->get();

        return view('bendahara.finance.dashboard', compact(
            'sources', 'totalIncome', 'totalExpense', 'totalBalance',
            'pendingExpenses', 'recentExpenses'
        ));
    }
}
