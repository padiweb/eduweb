<x-simans-layout title="Setoran Kas">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-white">Setoran Kas</h1>
            <p class="text-gray-400 text-sm mt-0.5">Rekap uang yang diterima dan disetor ke rekening sekolah</p>
        </div>
        <button onclick="document.getElementById('modal-setoran').style.display='flex'"
            @if($sisaBelumSetor <= 0) disabled title="Tidak ada kas yang perlu disetor" @endif
            class="flex items-center gap-2 {{ $sisaBelumSetor > 0 ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-700 cursor-not-allowed opacity-50' }} text-white text-sm font-medium px-4 py-2 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Catat Setoran
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-lg px-4 py-3 mb-4">{{ $errors->first() }}</div>
    @endif

    {{-- Ringkasan kas --}}
    <div class="grid grid-cols-2 gap-4 mb-4">
        {{-- Kiri: total kas masuk dengan sisa per jenis --}}
        <div class="bg-gray-900 border border-white/5 rounded-xl p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-3">Total Kas Diterima</p>
            <div class="space-y-2.5">
                {{-- Tunai --}}
                <div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Tunai dari siswa</span>
                        <span class="text-white">Rp {{ number_format($totalTunaiDiterima, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs mt-0.5">
                        <span class="text-gray-600">Sudah disetor</span>
                        <span class="text-gray-500">- Rp {{ number_format($sudahSetorTunai, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs mt-0.5">
                        <span class="{{ $sisaTunai > 0 ? 'text-amber-400' : 'text-green-400' }}">Sisa belum disetor</span>
                        <span class="{{ $sisaTunai > 0 ? 'text-amber-400 font-semibold' : 'text-green-400' }}">
                            Rp {{ number_format($sisaTunai, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="border-t border-white/5"></div>
                {{-- Transfer --}}
                <div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Transfer dikonfirmasi</span>
                        <span class="text-white">Rp {{ number_format($totalTransferDiterima, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs mt-0.5">
                        <span class="text-gray-600">Sudah disetor</span>
                        <span class="text-gray-500">- Rp {{ number_format($sudahSetorTransfer, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs mt-0.5">
                        <span class="{{ $sisaTransfer > 0 ? 'text-amber-400' : 'text-green-400' }}">Sisa belum disetor</span>
                        <span class="{{ $sisaTransfer > 0 ? 'text-amber-400 font-semibold' : 'text-green-400' }}">
                            Rp {{ number_format($sisaTransfer, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="border-t border-white/5 pt-1 flex justify-between text-sm font-semibold">
                    <span class="text-white">Total diterima</span>
                    <span class="text-green-400">Rp {{ number_format($totalDiterima, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Kanan: saldo siap setor --}}
        <div class="bg-gray-900 border {{ $sisaBelumSetor > 0 ? 'border-amber-500/20' : 'border-white/5' }} rounded-xl p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-3">Status Setoran</p>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Sudah disetor ke bank</span>
                    <span class="text-white">Rp {{ number_format($totalSudahDisetor, 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-white/5 pt-2">
                    <p class="text-xs text-gray-500 mb-1">Saldo belum disetor</p>
                    <p class="text-2xl font-bold {{ $sisaBelumSetor > 0 ? 'text-amber-400' : 'text-gray-500' }}">
                        Rp {{ number_format($sisaBelumSetor, 0, ',', '.') }}
                    </p>
                    @if($sisaBelumSetor <= 0)
                        <p class="text-xs text-green-400 mt-1">✓ Semua kas sudah disetor</p>
                    @else
                        <div class="mt-2 space-y-1">
                            @if($sisaTunai > 0)
                            <p class="text-xs text-amber-400/80">· Tunai belum disetor: Rp {{ number_format($sisaTunai, 0, ',', '.') }}</p>
                            @endif
                            @if($sisaTransfer > 0)
                            <p class="text-xs text-amber-400/80">· Transfer belum disetor: Rp {{ number_format($sisaTransfer, 0, ',', '.') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Info hari ini --}}
    <div class="bg-gray-900 border border-white/5 rounded-xl px-4 py-3 mb-5 text-xs text-gray-500">
        Hari ini ({{ now()->format('d/m/Y') }}): tunai masuk
        <strong class="text-white">Rp {{ number_format($tunaiHariIni, 0, ',', '.') }}</strong>,
        transfer dikonfirmasi
        <strong class="text-white">Rp {{ number_format($transferHariIni, 0, ',', '.') }}</strong>
    </div>

    {{-- Filter tahun --}}
    <form method="GET" class="flex gap-3 mb-5">
        <select name="year" onchange="this.form.submit()"
            class="bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
            @foreach($academicYears as $y)
                <option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>
                    {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' (Aktif)' : '' }}
                </option>
            @endforeach
        </select>
    </form>

    <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
        @if($setorans->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-500">Belum ada catatan setoran.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Tanggal</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Kas Tunai</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Transfer</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Total Disetor</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Tujuan</th>
                            <th class="text-center text-xs text-gray-500 font-medium px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($setorans as $s)
                        <tr class="hover:bg-white/2 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-white font-medium">{{ $s->tanggal_setoran->format('d/m/Y') }}</p>
                                @if($s->no_referensi)
                                    <p class="text-xs text-gray-500">{{ $s->no_referensi }}</p>
                                @endif
                                @if($s->keterangan)
                                    <p class="text-xs text-gray-600">{{ $s->keterangan }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-400">Rp {{ number_format($s->total_tunai, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-400">Rp {{ number_format($s->total_transfer, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-white">{{ $s->total_setoran_formatted }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $s->fundSource->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($s->status === 'setor')
                                    <span class="text-xs bg-green-500/10 text-green-400 border border-green-500/20 px-2 py-0.5 rounded-full">Disetor</span>
                                @else
                                    <span class="text-xs bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-0.5 rounded-full">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    @if($s->status === 'draft')
                                        <form method="POST" action="{{ route('bendahara.setoran.confirm', $s) }}"
                                            onsubmit="return confirm('Konfirmasi setoran Rp {{ number_format($s->total_setoran,0,',','.') }}?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="text-xs bg-green-600/10 hover:bg-green-600/20 border border-green-500/20 text-green-400 px-3 py-1 rounded-lg transition-colors">
                                                Konfirmasi
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('bendahara.setoran.destroy', $s) }}"
                                            onsubmit="return confirm('Hapus draft ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-gray-600 hover:text-red-400 transition-colors">Hapus</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-600">{{ $s->disetor_at?->format('d/m H:i') }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-white/5">{{ $setorans->links() }}</div>
        @endif
    </div>

    {{-- MODAL Catat Setoran --}}
    <div id="modal-setoran" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-white font-semibold">Catat Setoran Kas</h3>
                <button onclick="document.getElementById('modal-setoran').style.display='none'" class="text-gray-600 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-3 mb-4 text-xs">
                <p class="text-amber-400 font-semibold mb-1">Saldo siap disetor</p>
                <p class="text-2xl font-bold text-white">Rp {{ number_format($sisaBelumSetor, 0, ',', '.') }}</p>
                <p class="text-amber-400/70 mt-1">Maksimal yang bisa disetor sekarang</p>
            </div>

            <form method="POST" action="{{ route('bendahara.setoran.store') }}">
                @csrf
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Tanggal Setoran *</label>
                            <input type="date" name="tanggal_setoran" required value="{{ date('Y-m-d') }}"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Tahun Ajaran *</label>
                            <select name="academic_year_id" required
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                @foreach($academicYears as $y)
                                    <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>
                                        {{ $y->name }} S{{ $y->semester }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Disetor ke *</label>
                        <select name="fund_source_id" required
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            <option value="">-- Pilih rekening tujuan --</option>
                            @foreach($fundSources as $fs)
                                <option value="{{ $fs->id }}">{{ $fs->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">
                                Kas Tunai (Rp)
                                <span class="text-gray-600">· diterima: Rp {{ number_format($totalTunaiDiterima, 0, ',', '.') }}</span>
                            </label>
                            <input type="text" name="total_tunai" id="input-tunai"
                                value="{{ number_format($tunaiHariIni, 0, ',', '.') }}"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none transition-colors"
                                oninput="formatRibuan(this); cekKolom(this, {{ $totalTunaiDiterima }}, 'err-tunai'); hitungTotal()"
                                placeholder="0">
                            <p id="err-tunai" class="text-xs text-amber-400 mt-1 hidden">
                                ⚠ Melebihi total tunai diterima (Rp {{ number_format($totalTunaiDiterima, 0, ',', '.') }})
                            </p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">
                                Transfer (Rp)
                                <span class="text-gray-600">· dikonfirmasi: Rp {{ number_format($totalTransferDiterima, 0, ',', '.') }}</span>
                            </label>
                            <input type="text" name="total_transfer" id="input-transfer"
                                value="{{ number_format($transferHariIni, 0, ',', '.') }}"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none transition-colors"
                                oninput="formatRibuan(this); cekKolom(this, {{ $totalTransferDiterima }}, 'err-transfer'); hitungTotal()"
                                placeholder="0">
                            <p id="err-transfer" class="text-xs text-amber-400 mt-1 hidden">
                                ⚠ Melebihi total transfer dikonfirmasi (Rp {{ number_format($totalTransferDiterima, 0, ',', '.') }})
                            </p>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Total yang Disetor (Rp) *</label>
                        <input type="text" name="total_setoran" id="total-setoran" required
                            value="{{ number_format($sisaBelumSetor, 0, ',', '.') }}"
                            oninput="formatRibuan(this); cekSaldo(this)"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none transition-colors"
                            placeholder="0">
                        <div id="info-saldo" class="mt-1">
                            <p class="text-xs text-gray-600">
                                Maks: <strong class="text-white">Rp {{ number_format($sisaBelumSetor, 0, ',', '.') }}</strong> (saldo belum disetor)
                            </p>
                        </div>
                        <p id="error-saldo" class="text-xs text-red-400 mt-1 hidden">
                            ⚠ Melebihi saldo! Maks Rp {{ number_format($sisaBelumSetor, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">No. Slip Bank</label>
                            <input type="text" name="no_referensi" placeholder="Opsional"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Keterangan</label>
                            <input type="text" name="keterangan" placeholder="Opsional"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-setoran').style.display='none'"
                        class="flex-1 bg-gray-800 text-gray-300 text-sm py-2 rounded-lg">Batal</button>
                    <button type="submit" id="btn-simpan-setoran" class="flex-1 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium py-2 rounded-lg">
                        Simpan Draft
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('modal-setoran').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
    var maxSetor = {{ $sisaBelumSetor }};

    // Konversi string ribuan (1.000.000) ke integer
    function parseRibuan(val) {
        return parseInt((val || '0').replace(/\./g, '')) || 0;
    }

    // Format angka ke string ribuan: 1000000 -> 1.000.000
    function toRibuan(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Auto-format input saat mengetik
    function formatRibuan(input) {
        var pos    = input.selectionStart;
        var oldLen = input.value.length;
        var raw    = parseRibuan(input.value);
        if (isNaN(raw) || raw < 0) raw = 0;
        input.value = raw > 0 ? toRibuan(raw) : '';
        // Pertahankan posisi cursor
        var newLen = input.value.length;
        input.setSelectionRange(pos + (newLen - oldLen), pos + (newLen - oldLen));
    }

    function cekKolom(input, maxVal, errId) {
        var val   = parseRibuan(input.value);
        var errEl = document.getElementById(errId);
        if (val > maxVal) {
            errEl.classList.remove('hidden');
            input.classList.add('border-amber-500');
            input.classList.remove('border-white/10');
        } else {
            errEl.classList.add('hidden');
            input.classList.remove('border-amber-500');
            input.classList.add('border-white/10');
        }
    }

    function cekSaldo(input) {
        var val       = parseRibuan(input.value);
        var errEl     = document.getElementById('error-saldo');
        var btnSubmit = document.getElementById('btn-simpan-setoran');
        var inputEl   = document.getElementById('total-setoran');

        if (val > maxSetor) {
            errEl.classList.remove('hidden');
            inputEl.classList.add('border-red-500');
            inputEl.classList.remove('border-white/10');
            if (btnSubmit) btnSubmit.disabled = true;
        } else {
            errEl.classList.add('hidden');
            inputEl.classList.remove('border-red-500');
            inputEl.classList.add('border-white/10');
            if (btnSubmit) btnSubmit.disabled = false;
        }
    }

    function hitungTotal() {
        var tunai    = parseRibuan(document.querySelector('[name=total_tunai]').value);
        var transfer = parseRibuan(document.querySelector('[name=total_transfer]').value);
        var total    = Math.min(tunai + transfer, maxSetor);
        var el       = document.getElementById('total-setoran');
        el.value     = total > 0 ? toRibuan(total) : '';
        cekSaldo(el);
    }

    // Konversi semua input format ribuan ke integer sebelum submit
    document.querySelector('form').addEventListener('submit', function() {
        ['total_tunai','total_transfer','total_setoran'].forEach(function(name) {
            var el = document.querySelector('[name=' + name + ']');
            if (el) el.value = parseRibuan(el.value);
        });
    });
    </script>

</x-simans-layout>
