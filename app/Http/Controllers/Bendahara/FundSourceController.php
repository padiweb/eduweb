<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FundIncome;
use App\Models\FundSource;
use App\Models\KasSetoran;
use App\Models\PaymentTransaction;
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

    // ── Setoran Kas ──────────────────────────────────────────────────────────

    public function setoranIndex(Request $request)
    {
        $school = $this->school();

        $yearId = $request->filled('year')
            ? (int) $request->year
            : AcademicYear::where('school_id', $school->id)->where('is_active', true)->value('id');

        $setorans = KasSetoran::where('school_id', $school->id)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->with(['fundSource', 'createdBy'])
            ->orderByDesc('tanggal_setoran')
            ->paginate(20)->withQueryString();

        // Total penerimaan tunai & transfer yang sudah dikonfirmasi
        $totalTunaiDiterima = PaymentTransaction::where('school_id', $school->id)
            ->whereIn('channel', ['cash', 'scholarship_cash'])
            ->where('status', 'approved')
            ->sum('amount');

        $totalTransferDiterima = PaymentTransaction::where('school_id', $school->id)
            ->where('channel', 'transfer')
            ->where('status', 'approved')
            ->sum('amount');

        // Hitung sudah disetor per jenis dari kolom total_tunai & total_transfer
        $setoranDone = KasSetoran::where('school_id', $school->id)
            ->where('status', 'setor')
            ->selectRaw('SUM(total_tunai) as tunai, SUM(total_transfer) as transfer, SUM(total_setoran) as total')
            ->first();

        $sudahSetorTunai    = (int) ($setoranDone->tunai    ?? 0);
        $sudahSetorTransfer = (int) ($setoranDone->transfer ?? 0);
        $totalSudahDisetor  = (int) ($setoranDone->total    ?? 0);
        $totalDiterima      = $totalTunaiDiterima + $totalTransferDiterima;

        // Sisa per jenis = diterima - sudah disetor per jenis
        $sisaTunai    = max(0, $totalTunaiDiterima    - $sudahSetorTunai);
        $sisaTransfer = max(0, $totalTransferDiterima - $sudahSetorTransfer);

        // Saldo belum disetor = sisa tunai + sisa transfer
        // (bukan totalDiterima - totalSudahDisetor, karena total_setoran bisa berbeda dari tunai+transfer)
        $sisaBelumSetor = $sisaTunai + $sisaTransfer;

        // Rincian hari ini
        $today = now()->toDateString();
        $tunaiHariIni    = PaymentTransaction::where('school_id', $school->id)
            ->whereIn('channel', ['cash', 'scholarship_cash'])
            ->where('status', 'approved')
            ->whereDate('confirmed_at', $today)->sum('amount');
        $transferHariIni = PaymentTransaction::where('school_id', $school->id)
            ->where('channel', 'transfer')->where('status', 'approved')
            ->whereDate('confirmed_at', $today)->sum('amount');

        $fundSources   = FundSource::where('school_id', $school->id)->where('is_active', true)->get();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderByDesc('is_active')->get();

        return view('bendahara.fund-sources.setoran', compact(
            'setorans', 'totalTunaiDiterima', 'totalTransferDiterima',
            'sudahSetorTunai', 'sudahSetorTransfer', 'totalSudahDisetor',
            'sisaTunai', 'sisaTransfer', 'sisaBelumSetor', 'totalDiterima',
            'tunaiHariIni', 'transferHariIni',
            'fundSources', 'academicYears', 'yearId'
        ));
    }

    public function setoranStore(Request $request)
    {
        $data = $request->validate([
            'fund_source_id'   => 'required|exists:fund_sources,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'tanggal_setoran'  => 'required|date',
            'total_tunai'      => 'required|integer|min:0',
            'total_transfer'   => 'required|integer|min:0',
            'total_setoran'    => 'required|integer|min:1',
            'keterangan'       => 'nullable|string|max:255',
            'no_referensi'     => 'nullable|string|max:50',
        ]);

        // Validasi: total_setoran tidak boleh melebihi saldo belum disetor
        $school = $this->school();
        $totalDiterima = PaymentTransaction::where('school_id', $school->id)
            ->whereIn('channel', ['cash', 'transfer', 'scholarship_cash'])
            ->where('status', 'approved')
            ->sum('amount');
        $totalSudahDisetor = KasSetoran::where('school_id', $school->id)
            ->where('status', 'setor')
            ->sum('total_setoran');
        $sisaBelumSetor = max(0, $totalDiterima - $totalSudahDisetor);

        if ($data['total_setoran'] > $sisaBelumSetor) {
            return back()
                ->withErrors(['total_setoran' => 'Jumlah setoran (Rp ' . number_format($data['total_setoran'],0,',','.') . ') melebihi saldo yang tersedia (Rp ' . number_format($sisaBelumSetor,0,',','.') . ').'])
                ->withInput();
        }

        $school = $this->school();
        $data['school_id']  = $school->id;
        $data['created_by'] = auth()->id();
        $data['status']     = 'draft';

        KasSetoran::create($data);

        return back()->with('success', 'Setoran kas Rp ' . number_format($data['total_setoran'],0,',','.') . ' berhasil dicatat.');
    }

    public function setoranConfirm(KasSetoran $setoran)
    {
        if ($setoran->school_id !== $this->school()->id) abort(403);
        if ($setoran->status === 'setor') return back()->withErrors(['setoran' => 'Sudah disetor.']);

        $setoran->update(['status' => 'setor', 'disetor_at' => now()]);

        // Catat ke FundIncome sumber dana tujuan
        FundIncome::create([
            'school_id'        => $this->school()->id,
            'fund_source_id'   => $setoran->fund_source_id,
            'academic_year_id' => $setoran->academic_year_id,
            'description'      => 'Setoran kas ' . $setoran->tanggal_setoran->format('d/m/Y'),
            'amount'           => $setoran->total_setoran,
            'income_date'      => $setoran->tanggal_setoran->toDateString(),
            'period_label'     => $setoran->tanggal_setoran->format('F Y'),
            'reference_number' => $setoran->no_referensi,
            'notes'            => $setoran->keterangan,
            'created_by'       => auth()->id(),
        ]);

        return back()->with('success', 'Setoran dikonfirmasi dan masuk ke pemasukan resmi.');
    }

    public function setoranDestroy(KasSetoran $setoran)
    {
        if ($setoran->school_id !== $this->school()->id) abort(403);
        if ($setoran->status === 'setor') return back()->withErrors(['setoran' => 'Setoran sudah dikonfirmasi, tidak dapat dihapus.']);
        $setoran->delete();
        return back()->with('success', 'Draft setoran dihapus.');
    }

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
