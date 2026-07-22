<x-simans-layout title="Tagihan Siswa">

    <div class="mb-6">
        <a href="{{ route('bendahara.bills.index') }}?year={{ $yearId }}"
            class="text-gray-500 hover:text-gray-900 text-sm flex items-center gap-1 mb-4 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali ke Daftar
        </a>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $student->name }}</h1>
                <p class="text-gray-500 text-sm mt-0.5">{{ $student->nis ?? '-' }}</p>
            </div>
            <a href="{{ route('bendahara.bills.create') }}"
                class="text-xs bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 px-3 py-2 rounded-lg transition-colors">
                Buat Tagihan Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-lg px-4 py-3 mb-4">{{ $errors->first() }}</div>
    @endif

    {{-- Ringkasan --}}
    <div class="grid grid-cols-3 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
            <p class="text-xs text-gray-400 mb-1">Total Tagihan</p>
            <p class="text-base font-bold text-gray-900">Rp {{ number_format($totalBilled, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
            <p class="text-xs text-gray-400 mb-1">Sudah Dibayar</p>
            <p class="text-base font-bold text-green-400">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border {{ $totalRemaining > 0 ? 'border-red-500/20' : 'border-gray-200' }} rounded-xl px-4 py-3">
            <p class="text-xs text-gray-400 mb-1">Sisa Tagihan</p>
            <p class="text-base font-bold {{ $totalRemaining > 0 ? 'text-red-400' : 'text-gray-400' }}">
                {{ $totalRemaining > 0 ? 'Rp ' . number_format($totalRemaining, 0, ',', '.') : 'Lunas semua' }}
            </p>
        </div>
    </div>

    {{-- Filter tahun --}}
    <form method="GET" class="mb-5">
        <select name="year" onchange="this.form.submit()"
            class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
            @foreach($academicYears as $y)
                <option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>
                    {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' (Aktif)' : '' }}
                </option>
            @endforeach
        </select>
    </form>

    {{-- Daftar tagihan --}}
    @if($bills->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl px-5 py-12 text-center">
            <p class="text-gray-400">Belum ada tagihan untuk tahun ajaran ini.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($bills as $bill)
            @php
                $remaining = $bill->amount_remaining;
                $cl = ['unpaid'=>'red','partial'=>'amber','paid'=>'green','waived'=>'blue'][$bill->status] ?? 'gray';
                $lb = ['unpaid'=>'Belum bayar','partial'=>'Cicilan','paid'=>'Lunas','waived'=>'Dibebaskan'][$bill->status] ?? '-';
            @endphp
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                {{-- Header --}}
                <div class="px-5 py-4 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-semibold text-gray-900">{{ $bill->paymentType->name }}</p>
                            <span class="text-xs bg-{{ $cl }}-500/10 text-{{ $cl }}-400 border border-{{ $cl }}-500/20 px-2 py-0.5 rounded-full">
                                {{ $lb }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $bill->period_label }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-900">Rp {{ number_format($bill->amount_billed, 0, ',', '.') }}</p>
                        @if($remaining > 0 && $bill->status !== 'waived')
                            <p class="text-xs text-red-400">Sisa Rp {{ number_format($remaining, 0, ',', '.') }}</p>
                        @endif
                        @if($bill->amount_discount > 0)
                            <p class="text-xs text-blue-400">Disc Rp {{ number_format($bill->amount_discount, 0, ',', '.') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Riwayat --}}
                @if($bill->transactions->isNotEmpty())
                <div class="border-t border-gray-200 px-5 py-2 space-y-0.5">
                    @foreach($bill->transactions as $trx)
                    <div class="flex items-center justify-between text-xs py-1">
                        <span class="text-gray-400">
                            {{ $trx->created_at->format('d/m/Y H:i') }}
                            · @php
                                $channelLabel = match($trx->channel) {
                                    'cash'               => 'Tunai',
                                    'transfer'           => 'Transfer',
                                    'scholarship_cash'   => 'Beasiswa Dana',
                                    'scholarship_waiver' => 'Beasiswa Potong',
                                    'scholarship'        => 'Beasiswa',
                                    default              => ucfirst($trx->channel),
                                };
                            @endphp {{ $channelLabel }}
                            @if($trx->cashier_notes) · {{ $trx->cashier_notes }} @endif
                        </span>
                        <div class="flex items-center gap-3">
                            <span class="text-green-400 font-medium">+ Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
                            <a href="{{ route('bendahara.transactions.struk', $trx) }}" target="_blank"
                                class="text-gray-400 hover:text-gray-900 transition-colors" title="Cetak struk pembayaran ini">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Aksi --}}
                @if($remaining > 0)
                <div class="border-t border-gray-200 px-5 py-3 flex items-center gap-3">
                    <button type="button"
                        onclick="bukaModalBayar('{{ route('bendahara.bills.cash', $bill) }}', {{ $remaining }}, '{{ addslashes($bill->paymentType->name) }}')"
                        class="text-sm bg-emerald-700 hover:bg-blue-700 text-gray-900 px-4 py-1.5 rounded-lg transition-colors">
                        Bayar
                    </button>
                    <button type="button"
                        onclick="bukaModalKeringanan({{ $bill->id }}, {{ $remaining }}, '{{ addslashes($bill->paymentType->name) }}')"
                        class="text-xs bg-amber-600/20 hover:bg-amber-600/30 border border-amber-500/30 text-amber-400 px-3 py-1.5 rounded-lg transition-colors">
                        Keringanan
                    </button>
                    <a href="{{ route('bendahara.bills.edit', $bill) }}"
                        class="text-sm text-gray-400 hover:text-gray-900 transition-colors">Edit</a>
                    <form method="POST" action="{{ route('bendahara.bills.destroy', $bill) }}"
                        onsubmit="return confirm('Hapus tagihan ini?')" class="ml-auto">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-gray-900 hover:text-red-400 transition-colors">Hapus</button>
                    </form>
                </div>
                @else
                <div class="border-t border-gray-200 px-5 py-2.5 flex items-center justify-between">
                    <a href="{{ route('bendahara.bills.receipt', $bill) }}" target="_blank"
                        class="text-xs text-gray-400 hover:text-gray-900 transition-colors">Cetak Kwitansi</a>
                    <a href="{{ route('bendahara.bills.edit', $bill) }}" class="text-xs text-gray-400 hover:text-gray-900 transition-colors">Edit</a>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    @endif

    {{-- MODAL BAYAR — pakai JS vanilla, bukan Alpine x-init agar tidak auto-muncul --}}
    <div id="modal-bayar" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
        <div class="bg-white border border-gray-200 rounded-2xl w-full max-w-sm p-6">

            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Catat Pembayaran</h3>
                    <p id="modal-nama" class="text-xs text-gray-400 mt-0.5"></p>
                </div>
                <button type="button" onclick="tutupModal()"
                    class="text-gray-400 hover:text-gray-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <p class="text-xs text-gray-400 mb-0.5">Sisa tagihan</p>
            <p id="modal-sisa" class="text-lg font-bold text-gray-900 mb-4"></p>

            {{-- Tab pilihan --}}
            <div class="flex gap-2 mb-4" id="tab-wrap">
                <button type="button" onclick="gantiTab('full')" id="tab-full"
                    class="flex-1 text-xs font-medium py-2 rounded-lg border bg-gray-100 border-white/30 text-gray-900 transition-colors">
                    Lunas
                </button>
                <button type="button" onclick="gantiTab('partial')" id="tab-partial"
                    class="flex-1 text-xs font-medium py-2 rounded-lg border bg-white border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">
                    Cicilan
                </button>
                @if($discounts->isNotEmpty())
                <button type="button" onclick="gantiTab('scholarship')" id="tab-scholarship"
                    class="flex-1 text-xs font-medium py-2 rounded-lg border bg-white border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">
                    Beasiswa
                </button>
                @endif
            </div>

            {{-- Form — action diisi oleh JS --}}
            <form id="form-bayar" method="POST" action="">
                @csrf
                <input type="hidden" name="pay_type" id="input-pay-type" value="full">

                {{-- Panel lunas --}}
                <div id="panel-full" class="mb-4 bg-white rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-500">Mencatat pelunasan penuh</p>
                    <p id="panel-sisa-full" class="text-sm font-bold text-gray-900 mt-1"></p>
                </div>

                {{-- Panel cicilan --}}
                <div id="panel-partial" class="mb-4 hidden">
                    <label class="text-xs text-gray-500 mb-1 block">Nominal yang dibayar (Rp)</label>
                    <input type="number" name="amount" id="input-amount" min="1"
                        placeholder="Masukkan nominal"
                        class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2.5 focus:border-purple-500 focus:outline-none">
                    <p class="text-xs text-gray-400 mt-1">Sisa akan tetap tercatat sebagai tunggakan</p>
                </div>

                {{-- Panel beasiswa --}}
                @if($discounts->isNotEmpty())
                <div id="panel-scholarship" class="mb-4 hidden">
                    <label class="text-xs text-gray-500 mb-1 block">Pilih beasiswa</label>
                    <select name="discount_id" id="select-discount"
                        onchange="onDiscountChange(this)"
                        class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2.5 focus:border-purple-500 focus:outline-none">
                        <option value="">-- Pilih --</option>
                        @foreach($discounts as $disc)
                        @php
                            $stype = $disc->scholarship_type ?? 'cash';
                            $nilaiLabel = $disc->discount_type === 'percent'
                                ? $disc->discount_value . '%'
                                : 'Rp ' . number_format($disc->discount_value, 0, ',', '.');
                        @endphp
                            <option value="{{ $disc->id }}"
                                data-type="{{ $stype }}"
                                data-dtype="{{ $disc->discount_type }}"
                                data-value="{{ $disc->discount_value }}">
                                {{ $disc->name }} ({{ $nilaiLabel }}) — {{ $stype === 'cash' ? 'Dana masuk kas' : 'Potongan tagihan' }}
                            </option>
                        @endforeach
                    </select>
                    {{-- Info setelah pilih beasiswa --}}
                    <div id="info-discount" class="mt-2 hidden">
                        <div id="info-waiver" class="hidden bg-purple-500/10 border border-purple-500/20 rounded-lg px-3 py-2 text-xs">
                            <p class="text-purple-400">Tagihan otomatis dipotong sebesar:</p>
                            <p class="text-gray-900 font-semibold text-sm mt-0.5" id="info-waiver-nominal"></p>
                            <p class="text-purple-400/60 mt-0.5">Potongan ini tidak masuk pemasukan kas</p>
                        </div>
                        <div id="info-cash" class="hidden">
                            <label class="text-xs text-gray-500 mb-1 mt-2 block">Nominal yang dicairkan (Rp)</label>
                            <input type="number" name="discount_amount" id="input-discount-amount"
                                min="1" placeholder="Nominal beasiswa yang dibayarkan"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2.5 focus:border-purple-500 focus:outline-none">
                            <p class="text-xs text-gray-400 mt-1" id="info-cash-hint">Bisa lebih kecil dari nilai beasiswa jika tagihan lebih kecil</p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mb-4">
                    <label class="text-xs text-gray-500 mb-1 block">Catatan (opsional)</label>
                    <input type="text" name="cashier_notes" placeholder="Catatan pembayaran..."
                        class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                </div>

                <div class="flex gap-2">
                    <button type="submit" id="btn-submit"
                        class="flex-1 bg-emerald-700 hover:bg-blue-700 text-gray-900 text-sm font-semibold py-2.5 rounded-lg transition-colors">
                        Konfirmasi Lunas
                    </button>
                    <button type="button"
                        id="btn-submit-print"
                        title="Catat lalu cetak struk"
                        onclick="submitAndPrint()"
                        class="bg-gray-100 hover:bg-gray-600 text-gray-900 text-xs px-3 py-2.5 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    var activeTab = 'full';

    function bukaModalBayar(actionUrl, sisa, nama) {
        window._currentSisa = sisa;
        document.getElementById('form-bayar').action    = actionUrl;
        document.getElementById('modal-nama').textContent   = nama;
        document.getElementById('modal-sisa').textContent   = 'Rp ' + sisa.toLocaleString('id-ID');
        document.getElementById('panel-sisa-full').textContent = 'Rp ' + sisa.toLocaleString('id-ID');
        document.getElementById('input-amount').max        = sisa;
        gantiTab('full');
        document.getElementById('modal-bayar').style.display = 'flex';
    }

    // Saat pilih beasiswa - tampilkan info sesuai tipe
    function onDiscountChange(sel) {
        var opt      = sel.options[sel.selectedIndex];
        var infoDiv  = document.getElementById('info-discount');
        var waiver   = document.getElementById('info-waiver');
        var cash     = document.getElementById('info-cash');
        var nomLabel = document.getElementById('info-waiver-nominal');
        var hint     = document.getElementById('info-cash-hint');
        var amtInput = document.getElementById('input-discount-amount');

        if (!opt.value) {
            infoDiv.classList.add('hidden');
            waiver.classList.add('hidden');
            cash.classList.add('hidden');
            return;
        }

        var stype  = opt.dataset.type;
        var dtype  = opt.dataset.dtype;
        var val    = parseFloat(opt.dataset.value) || 0;
        var sisa   = window._currentSisa || 0;

        infoDiv.classList.remove('hidden');

        if (stype === 'waiver') {
            waiver.classList.remove('hidden');
            cash.classList.add('hidden');
            // Hitung potongan
            var potongan = dtype === 'percent'
                ? Math.round(sisa * val / 100)
                : Math.min(val, sisa);
            potongan = Math.min(potongan, sisa);
            nomLabel.textContent = 'Rp ' + potongan.toLocaleString('id-ID');
        } else {
            waiver.classList.add('hidden');
            cash.classList.remove('hidden');
            // Set nilai default dan max
            var defaultVal = dtype === 'percent'
                ? Math.round(sisa * val / 100)
                : val;
            amtInput.value = Math.min(defaultVal, sisa);
            amtInput.max   = sisa;
            hint.textContent = 'Maks Rp ' + sisa.toLocaleString('id-ID') + ' (sisa tagihan). Nilai beasiswa: ' +
                (dtype === 'percent' ? val + '%' : 'Rp ' + val.toLocaleString('id-ID'));
        }
    }

    function tutupModal() {
        document.getElementById('modal-bayar').style.display = 'none';
        document.getElementById('form-bayar').reset();
        document.getElementById('info-discount').classList.add('hidden');
        var w = document.getElementById('info-waiver');
        var ca = document.getElementById('info-cash');
        if(w) w.classList.add('hidden');
        if(ca) ca.classList.add('hidden');
    }

    function gantiTab(tab) {
        activeTab = tab;
        // Reset semua tab
        ['full','partial','scholarship'].forEach(function(t) {
            var btn = document.getElementById('tab-' + t);
            var panel = document.getElementById('panel-' + t);
            if (btn) {
                btn.className = btn.className
                    .replace('bg-gray-100 border-white/30 text-gray-900','')
                    .replace('bg-white border-gray-200 text-gray-500','')
                    .trim();
            }
            if (panel) panel.classList.add('hidden');
        });
        // Aktifkan tab yang dipilih
        var activeBtn = document.getElementById('tab-' + tab);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white','border-gray-200','text-gray-500');
            activeBtn.classList.add('bg-gray-100','border-white/30','text-gray-900');
        }
        var activePanel = document.getElementById('panel-' + tab);
        if (activePanel) activePanel.classList.remove('hidden');
        // Update hidden input dan teks tombol
        document.getElementById('input-pay-type').value = tab;
        var labels = {full:'Konfirmasi Lunas', partial:'Catat Cicilan', scholarship:'Bayar dengan Beasiswa'};
        document.getElementById('btn-submit').textContent = labels[tab] || 'Simpan';
    }

    // Tutup modal jika klik backdrop
    // Tombol cetak: set flag print, submit form biasa, server redirect ke struk
    function submitAndPrint() {
        var input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'print_receipt';
        input.value = '1';
        document.getElementById('form-bayar').appendChild(input);
        document.getElementById('form-bayar').submit();
    }

    document.getElementById('modal-bayar').addEventListener('click', function(e) {
        if (e.target === this) tutupModal();
    });
    </script>
    @endpush

{{-- MODAL Keringanan / Pembebasan Sebagian --}}
<div id="modal-keringanan" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
    <div class="bg-white border border-gray-200 rounded-2xl w-full max-w-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-gray-900 font-semibold">Keringanan Tagihan</h3>
                <p class="text-xs text-gray-400 mt-0.5" id="ket-keringanan-nama"></p>
            </div>
            <button onclick="document.getElementById('modal-keringanan').style.display='none'"
                class="text-gray-400 hover:text-gray-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Info sisa tagihan --}}
        <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl px-4 py-3 mb-4">
            <p class="text-xs text-amber-400 mb-1">Sisa tagihan yang belum dibayar</p>
            <p class="text-xl font-bold text-gray-900" id="sisa-keringanan"></p>
            <p class="text-xs text-amber-400/70 mt-1">Jumlah keringanan tidak boleh melebihi sisa ini</p>
        </div>

        <form id="form-keringanan" method="POST" action="">
            @csrf @method('PATCH')
            <div class="space-y-3">
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Jumlah yang dibebaskan (Rp) *</label>
                    <input type="number" name="waive_amount" id="input-waive-amount"
                        min="1" required
                        class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-amber-500 focus:outline-none"
                        placeholder="Contoh: 100000"
                        oninput="cekWaiveAmount(this)">
                    <p class="text-xs text-gray-400 mt-1">
                        Kosongkan tagihan: <button type="button" id="btn-waive-all"
                            onclick="isiWaiveAll()"
                            class="text-amber-400 hover:text-amber-300 underline">Bebaskan semua sisa</button>
                    </p>
                    <p id="err-waive" class="text-xs text-red-400 mt-1 hidden"></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Alasan / Keterangan *</label>
                    <textarea name="reason" rows="2" required
                        placeholder="Contoh: Keringanan karena kondisi ekonomi keluarga..."
                        class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-amber-500 focus:outline-none resize-none"></textarea>
                    <p class="text-xs text-gray-400 mt-1">Alasan wajib diisi untuk keperluan audit keuangan</p>
                </div>
                <div class="bg-blue-500/5 border border-blue-500/15 rounded-lg px-3 py-2 text-xs text-blue-300">
                    Jumlah yang dibebaskan <strong>tidak akan masuk ke pemasukan kas sekolah</strong>.
                    Sisa tagihan akan berkurang sesuai jumlah keringanan.
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="button"
                    onclick="document.getElementById('modal-keringanan').style.display='none'"
                    class="flex-1 bg-white text-gray-600 text-sm py-2 rounded-lg">Batal</button>
                <button type="submit" id="btn-submit-keringanan"
                    class="flex-1 bg-amber-600 hover:bg-amber-700 text-gray-900 text-sm font-medium py-2 rounded-lg">
                    Berikan Keringanan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
var _sisaKeringanan = 0;

function bukaModalKeringanan(billId, sisa, nama) {
    _sisaKeringanan = sisa;
    document.getElementById('form-keringanan').action = '/bendahara/bills/' + billId + '/waive-partial';
    document.getElementById('ket-keringanan-nama').textContent = nama;
    document.getElementById('sisa-keringanan').textContent = 'Rp ' + sisa.toLocaleString('id-ID');
    document.getElementById('input-waive-amount').value = '';
    document.getElementById('input-waive-amount').max = sisa;
    document.getElementById('err-waive').classList.add('hidden');
    document.getElementById('modal-keringanan').style.display = 'flex';
}

function cekWaiveAmount(input) {
    var val   = parseInt(input.value) || 0;
    var errEl = document.getElementById('err-waive');
    var btn   = document.getElementById('btn-submit-keringanan');
    if (val > _sisaKeringanan) {
        errEl.textContent = '⚠ Melebihi sisa tagihan (Rp ' + _sisaKeringanan.toLocaleString('id-ID') + ')';
        errEl.classList.remove('hidden');
        input.value = _sisaKeringanan;
        btn.disabled = false;
    } else {
        errEl.classList.add('hidden');
        btn.disabled = false;
    }
}

function isiWaiveAll() {
    document.getElementById('input-waive-amount').value = _sisaKeringanan;
}

document.getElementById('modal-keringanan').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

</x-simans-layout>
