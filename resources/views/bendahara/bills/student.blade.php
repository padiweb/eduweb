<x-simans-layout title="Tagihan Siswa">

    <div class="mb-6">
        <a href="{{ route('bendahara.bills.index') }}?year={{ $yearId }}"
            class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-4 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali ke Daftar
        </a>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-white">{{ $student->name }}</h1>
                <p class="text-gray-400 text-sm mt-0.5">{{ $student->nis ?? '-' }}</p>
            </div>
            <a href="{{ route('bendahara.bills.create') }}"
                class="text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 border border-white/10 px-3 py-2 rounded-lg transition-colors">
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
        <div class="bg-gray-900 border border-white/5 rounded-xl px-4 py-3">
            <p class="text-xs text-gray-500 mb-1">Total Tagihan</p>
            <p class="text-base font-bold text-white">Rp {{ number_format($totalBilled, 0, ',', '.') }}</p>
        </div>
        <div class="bg-gray-900 border border-white/5 rounded-xl px-4 py-3">
            <p class="text-xs text-gray-500 mb-1">Sudah Dibayar</p>
            <p class="text-base font-bold text-green-400">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
        </div>
        <div class="bg-gray-900 border {{ $totalRemaining > 0 ? 'border-red-500/20' : 'border-white/5' }} rounded-xl px-4 py-3">
            <p class="text-xs text-gray-500 mb-1">Sisa Tagihan</p>
            <p class="text-base font-bold {{ $totalRemaining > 0 ? 'text-red-400' : 'text-gray-500' }}">
                {{ $totalRemaining > 0 ? 'Rp ' . number_format($totalRemaining, 0, ',', '.') : 'Lunas semua' }}
            </p>
        </div>
    </div>

    {{-- Filter tahun --}}
    <form method="GET" class="mb-5">
        <select name="year" onchange="this.form.submit()"
            class="bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
            @foreach($academicYears as $y)
                <option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>
                    {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' (Aktif)' : '' }}
                </option>
            @endforeach
        </select>
    </form>

    {{-- Daftar tagihan --}}
    @if($bills->isEmpty())
        <div class="bg-gray-900 border border-white/5 rounded-xl px-5 py-12 text-center">
            <p class="text-gray-500">Belum ada tagihan untuk tahun ajaran ini.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($bills as $bill)
            @php
                $remaining = $bill->amount_remaining;
                $cl = ['unpaid'=>'red','partial'=>'amber','paid'=>'green','waived'=>'blue'][$bill->status] ?? 'gray';
                $lb = ['unpaid'=>'Belum bayar','partial'=>'Cicilan','paid'=>'Lunas','waived'=>'Dibebaskan'][$bill->status] ?? '-';
            @endphp
            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                {{-- Header --}}
                <div class="px-5 py-4 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-semibold text-white">{{ $bill->paymentType->name }}</p>
                            <span class="text-xs bg-{{ $cl }}-500/10 text-{{ $cl }}-400 border border-{{ $cl }}-500/20 px-2 py-0.5 rounded-full">
                                {{ $lb }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $bill->period_label }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-white">Rp {{ number_format($bill->amount_billed, 0, ',', '.') }}</p>
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
                <div class="border-t border-white/5 px-5 py-2 space-y-0.5">
                    @foreach($bill->transactions as $trx)
                    <div class="flex items-center justify-between text-xs py-1">
                        <span class="text-gray-500">
                            {{ $trx->created_at->format('d/m/Y H:i') }}
                            · {{ $trx->channel === 'scholarship' ? 'Beasiswa' : 'Tunai' }}
                            @if($trx->cashier_notes) · {{ $trx->cashier_notes }} @endif
                        </span>
                        <span class="text-green-400 font-medium">+ Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Aksi --}}
                @if($remaining > 0)
                <div class="border-t border-white/5 px-5 py-3 flex items-center gap-3">
                    <button type="button"
                        onclick="bukaModalBayar('{{ route('bendahara.bills.cash', $bill) }}', {{ $remaining }}, '{{ addslashes($bill->paymentType->name) }}')"
                        class="text-sm bg-emerald-700 hover:bg-emerald-600 text-white px-4 py-1.5 rounded-lg transition-colors">
                        Bayar
                    </button>
                    <a href="{{ route('bendahara.bills.edit', $bill) }}"
                        class="text-sm text-gray-500 hover:text-white transition-colors">Edit</a>
                    <form method="POST" action="{{ route('bendahara.bills.destroy', $bill) }}"
                        onsubmit="return confirm('Hapus tagihan ini?')" class="ml-auto">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-gray-700 hover:text-red-400 transition-colors">Hapus</button>
                    </form>
                </div>
                @else
                <div class="border-t border-white/5 px-5 py-2.5 flex items-center justify-between">
                    <a href="{{ route('bendahara.bills.receipt', $bill) }}" target="_blank"
                        class="text-xs text-gray-500 hover:text-white transition-colors">Cetak Kwitansi</a>
                    <a href="{{ route('bendahara.bills.edit', $bill) }}" class="text-xs text-gray-600 hover:text-white transition-colors">Edit</a>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    @endif

    {{-- MODAL BAYAR — pakai JS vanilla, bukan Alpine x-init agar tidak auto-muncul --}}
    <div id="modal-bayar" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-sm p-6">

            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-white">Catat Pembayaran</h3>
                    <p id="modal-nama" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <button type="button" onclick="tutupModal()"
                    class="text-gray-600 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <p class="text-xs text-gray-500 mb-0.5">Sisa tagihan</p>
            <p id="modal-sisa" class="text-lg font-bold text-white mb-4"></p>

            {{-- Tab pilihan --}}
            <div class="flex gap-2 mb-4" id="tab-wrap">
                <button type="button" onclick="gantiTab('full')" id="tab-full"
                    class="flex-1 text-xs font-medium py-2 rounded-lg border bg-gray-700 border-white/30 text-white transition-colors">
                    Lunas
                </button>
                <button type="button" onclick="gantiTab('partial')" id="tab-partial"
                    class="flex-1 text-xs font-medium py-2 rounded-lg border bg-gray-900 border-white/10 text-gray-400 hover:text-white transition-colors">
                    Cicilan
                </button>
                @if($discounts->isNotEmpty())
                <button type="button" onclick="gantiTab('scholarship')" id="tab-scholarship"
                    class="flex-1 text-xs font-medium py-2 rounded-lg border bg-gray-900 border-white/10 text-gray-400 hover:text-white transition-colors">
                    Beasiswa
                </button>
                @endif
            </div>

            {{-- Form — action diisi oleh JS --}}
            <form id="form-bayar" method="POST" action="">
                @csrf
                <input type="hidden" name="pay_type" id="input-pay-type" value="full">

                {{-- Panel lunas --}}
                <div id="panel-full" class="mb-4 bg-gray-800 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-400">Mencatat pelunasan penuh</p>
                    <p id="panel-sisa-full" class="text-sm font-bold text-white mt-1"></p>
                </div>

                {{-- Panel cicilan --}}
                <div id="panel-partial" class="mb-4 hidden">
                    <label class="text-xs text-gray-400 mb-1 block">Nominal yang dibayar (Rp)</label>
                    <input type="number" name="amount" id="input-amount" min="1"
                        placeholder="Masukkan nominal"
                        class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2.5 focus:border-purple-500 focus:outline-none">
                    <p class="text-xs text-gray-600 mt-1">Sisa akan tetap tercatat sebagai tunggakan</p>
                </div>

                {{-- Panel beasiswa --}}
                @if($discounts->isNotEmpty())
                <div id="panel-scholarship" class="mb-4 hidden">
                    <label class="text-xs text-gray-400 mb-1 block">Pilih beasiswa</label>
                    <select name="discount_id"
                        class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2.5 focus:border-purple-500 focus:outline-none">
                        <option value="">-- Pilih --</option>
                        @foreach($discounts as $disc)
                            <option value="{{ $disc->id }}">
                                {{ $disc->name }}
                                ({{ $disc->discount_type === 'percent'
                                    ? $disc->discount_value . '%'
                                    : 'Rp ' . number_format($disc->discount_value, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="mb-4">
                    <label class="text-xs text-gray-400 mb-1 block">Catatan (opsional)</label>
                    <input type="text" name="cashier_notes" placeholder="Catatan pembayaran..."
                        class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                </div>

                <button type="submit" id="btn-submit"
                    class="w-full bg-emerald-700 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                    Konfirmasi Lunas
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    var activeTab = 'full';

    function bukaModalBayar(actionUrl, sisa, nama) {
        document.getElementById('form-bayar').action    = actionUrl;
        document.getElementById('modal-nama').textContent   = nama;
        document.getElementById('modal-sisa').textContent   = 'Rp ' + sisa.toLocaleString('id-ID');
        document.getElementById('panel-sisa-full').textContent = 'Rp ' + sisa.toLocaleString('id-ID');
        document.getElementById('input-amount').max        = sisa;
        gantiTab('full');
        document.getElementById('modal-bayar').style.display = 'flex';
    }

    function tutupModal() {
        document.getElementById('modal-bayar').style.display = 'none';
        document.getElementById('form-bayar').reset();
    }

    function gantiTab(tab) {
        activeTab = tab;
        // Reset semua tab
        ['full','partial','scholarship'].forEach(function(t) {
            var btn = document.getElementById('tab-' + t);
            var panel = document.getElementById('panel-' + t);
            if (btn) {
                btn.className = btn.className
                    .replace('bg-gray-700 border-white/30 text-white','')
                    .replace('bg-gray-900 border-white/10 text-gray-400','')
                    .trim();
            }
            if (panel) panel.classList.add('hidden');
        });
        // Aktifkan tab yang dipilih
        var activeBtn = document.getElementById('tab-' + tab);
        if (activeBtn) {
            activeBtn.classList.remove('bg-gray-900','border-white/10','text-gray-400');
            activeBtn.classList.add('bg-gray-700','border-white/30','text-white');
        }
        var activePanel = document.getElementById('panel-' + tab);
        if (activePanel) activePanel.classList.remove('hidden');
        // Update hidden input dan teks tombol
        document.getElementById('input-pay-type').value = tab;
        var labels = {full:'Konfirmasi Lunas', partial:'Catat Cicilan', scholarship:'Bayar dengan Beasiswa'};
        document.getElementById('btn-submit').textContent = labels[tab] || 'Simpan';
    }

    // Tutup modal jika klik backdrop
    document.getElementById('modal-bayar').addEventListener('click', function(e) {
        if (e.target === this) tutupModal();
    });
    </script>
    @endpush

</x-simans-layout>
