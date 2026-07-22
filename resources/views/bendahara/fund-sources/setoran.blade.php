<x-simans-layout title="Setoran Kas">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Setoran Kas</h1>
            <p class="text-gray-500 text-sm mt-0.5">Rekap uang yang diterima dan disetor ke rekening sekolah</p>
        </div>
        <button onclick="document.getElementById('modal-setoran').style.display='flex'"
            @if($sisaBelumSetor <= 0) disabled title="Tidak ada kas yang perlu disetor" @endif
            class="flex items-center gap-2 {{ $sisaBelumSetor > 0 ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-100 cursor-not-allowed opacity-50' }} text-white text-sm font-medium px-4 py-2 rounded-lg">
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
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-3">Total Kas Diterima</p>
            <div class="space-y-2.5">
                {{-- Tunai --}}
                <div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Tunai dari siswa</span>
                        <span class="text-gray-900">Rp {{ number_format($totalTunaiDiterima, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs mt-0.5">
                        <span class="text-gray-500">Sudah disetor</span>
                        <span class="text-gray-500">- Rp {{ number_format($sudahSetorTunai, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs mt-0.5">
                        <span class="{{ $sisaTunai > 0 ? 'text-amber-400' : 'text-green-400' }}">Sisa belum disetor</span>
                        <span class="{{ $sisaTunai > 0 ? 'text-amber-400 font-semibold' : 'text-green-400' }}">
                            Rp {{ number_format($sisaTunai, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="border-t border-gray-200"></div>
                {{-- Transfer --}}
                <div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Transfer dikonfirmasi</span>
                        <span class="text-gray-900">Rp {{ number_format($totalTransferDiterima, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs mt-0.5">
                        <span class="text-gray-500">Sudah disetor</span>
                        <span class="text-gray-500">- Rp {{ number_format($sudahSetorTransfer, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs mt-0.5">
                        <span class="{{ $sisaTransfer > 0 ? 'text-amber-400' : 'text-green-400' }}">Sisa belum disetor</span>
                        <span class="{{ $sisaTransfer > 0 ? 'text-amber-400 font-semibold' : 'text-green-400' }}">
                            Rp {{ number_format($sisaTransfer, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-1 flex justify-between text-sm font-semibold">
                    <span class="text-gray-900">Total diterima</span>
                    <span class="text-green-400">Rp {{ number_format($totalDiterima, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Kanan: saldo siap setor --}}
        <div class="bg-white border {{ $sisaBelumSetor > 0 ? 'border-amber-500/20' : 'border-gray-200' }} rounded-xl p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-3">Status Setoran</p>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Sudah disetor ke bank</span>
                    <span class="text-gray-900">Rp {{ number_format($totalSudahDisetor, 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-gray-200 pt-2">
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
    <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 mb-5 text-xs text-gray-500">
        Hari ini ({{ now()->format('d/m/Y') }}): tunai masuk
        <strong class="text-gray-900">Rp {{ number_format($tunaiHariIni, 0, ',', '.') }}</strong>,
        transfer dikonfirmasi
        <strong class="text-gray-900">Rp {{ number_format($transferHariIni, 0, ',', '.') }}</strong>
    </div>

    {{-- Filter tahun --}}
    <form method="GET" class="flex gap-3 mb-5">
        <select name="year" onchange="this.form.submit()"
            class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
            @foreach($academicYears as $y)
                <option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>
                    {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' (Aktif)' : '' }}
                </option>
            @endforeach
        </select>
    </form>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($setorans->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-500">Belum ada catatan setoran.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Tanggal</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Kas Tunai</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Transfer</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Total Disetor</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Tujuan</th>
                            <th class="text-center text-xs text-gray-500 font-medium px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($setorans as $s)
                        <tr class="hover:bg-white/2 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-gray-900 font-medium">{{ $s->tanggal_setoran->format('d/m/Y') }}</p>
                                @if($s->no_referensi)
                                    <p class="text-xs text-gray-500">{{ $s->no_referensi }}</p>
                                @endif
                                @if($s->keterangan)
                                    <p class="text-xs text-gray-500">{{ $s->keterangan }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500">Rp {{ number_format($s->total_tunai, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">Rp {{ number_format($s->total_transfer, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $s->total_setoran_formatted }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $s->fundSource->name ?? '-' }}</td>
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
                                            <button type="submit" class="text-xs text-gray-500 hover:text-red-400 transition-colors">Hapus</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-500">{{ $s->disetor_at?->format('d/m H:i') }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">{{ $setorans->links() }}</div>
        @endif
    </div>

    {{-- MODAL Catat Setoran --}}
    <div id="modal-setoran" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
        <div class="bg-white border border-gray-200 rounded-xl w-full max-w-md p-6 overflow-y-auto max-h-[90vh]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-900 font-semibold">Catat Setoran Kas</h3>
                <button onclick="document.getElementById('modal-setoran').style.display='none'" class="text-gray-500 hover:text-gray-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-3 mb-4 text-xs">
                <p class="text-amber-400 font-semibold mb-1">Saldo siap disetor</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($sisaBelumSetor, 0, ',', '.') }}</p>
                <p class="text-amber-400/70 mt-1">Maksimal yang bisa disetor sekarang</p>
            </div>

            <form method="POST" action="{{ route('bendahara.setoran.store') }}">
                @csrf
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Tanggal Setoran *</label>
                            <input type="date" name="tanggal_setoran" required value="{{ date('Y-m-d') }}"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Tahun Ajaran *</label>
                            <select name="academic_year_id" required
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                                @foreach($academicYears as $y)
                                    <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>
                                        {{ $y->name }} S{{ $y->semester }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Disetor ke *</label>
                        <select name="fund_source_id" required
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            <option value="">-- Pilih rekening tujuan --</option>
                            @foreach($fundSources as $fs)
                                <option value="{{ $fs->id }}">{{ $fs->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Kas Tunai --}}
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Kas Tunai (Rp)</label>
                            <div class="text-xs mb-1.5 space-y-0.5">
                                <div class="flex justify-between text-gray-500">
                                    <span>Diterima</span>
                                    <span>Rp {{ number_format($totalTunaiDiterima, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-gray-500">
                                    <span>Sudah disetor</span>
                                    <span>- Rp {{ number_format($sudahSetorTunai, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-semibold {{ $sisaTunai > 0 ? 'text-amber-400' : 'text-green-400' }}">
                                    <span>Sisa</span>
                                    <span>Rp {{ number_format($sisaTunai, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <input type="text" name="total_tunai" id="input-tunai"
                                value="{{ $sisaTunai > 0 ? number_format($sisaTunai, 0, ',', '.') : '0' }}"
                                @if($sisaTunai <= 0) readonly @endif
                                class="w-full bg-white border border-gray-200 text-sm rounded-lg px-3 py-2 focus:outline-none transition-colors {{ $sisaTunai <= 0 ? 'text-gray-500 cursor-not-allowed opacity-50' : 'text-gray-900 focus:border-blue-500' }}"
                                oninput="inputKas(this, maxTunai, 'err-tunai')"
                                placeholder="0">
                            <p id="err-tunai" class="text-xs text-red-400 mt-1 hidden">
                                ⚠ Maks Rp {{ number_format($sisaTunai, 0, ',', '.') }}
                            </p>
                            @if($sisaTunai <= 0)
                                <p class="text-xs text-green-400 mt-1">✓ Sudah disetor semua</p>
                            @endif
                        </div>

                        {{-- Transfer --}}
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Transfer (Rp)</label>
                            <div class="text-xs mb-1.5 space-y-0.5">
                                <div class="flex justify-between text-gray-500">
                                    <span>Dikonfirmasi</span>
                                    <span>Rp {{ number_format($totalTransferDiterima, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-gray-500">
                                    <span>Sudah disetor</span>
                                    <span>- Rp {{ number_format($sudahSetorTransfer, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-semibold {{ $sisaTransfer > 0 ? 'text-amber-400' : 'text-green-400' }}">
                                    <span>Sisa</span>
                                    <span>Rp {{ number_format($sisaTransfer, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <input type="text" name="total_transfer" id="input-transfer"
                                value="{{ $sisaTransfer > 0 ? number_format($sisaTransfer, 0, ',', '.') : '0' }}"
                                @if($sisaTransfer <= 0) readonly @endif
                                class="w-full bg-white border border-gray-200 text-sm rounded-lg px-3 py-2 focus:outline-none transition-colors {{ $sisaTransfer <= 0 ? 'text-gray-500 cursor-not-allowed opacity-50' : 'text-gray-900 focus:border-blue-500' }}"
                                oninput="inputKas(this, maxTransfer, 'err-transfer')"
                                placeholder="0">
                            <p id="err-transfer" class="text-xs text-red-400 mt-1 hidden">
                                ⚠ Maks Rp {{ number_format($sisaTransfer, 0, ',', '.') }}
                            </p>
                            @if($sisaTransfer <= 0)
                                <p class="text-xs text-green-400 mt-1">✓ Sudah disetor semua</p>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Total yang Disetor (Rp) *</label>
                        <input type="text" name="total_setoran" id="total-setoran" required
                            value="{{ number_format($sisaBelumSetor, 0, ',', '.') }}"
                            readonly
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 cursor-not-allowed"
                            placeholder="0">
                        <div id="info-saldo" class="mt-1">
                            <p class="text-xs text-gray-500">
                                Maks: <strong class="text-gray-900">Rp {{ number_format($sisaBelumSetor, 0, ',', '.') }}</strong> (saldo belum disetor)
                            </p>
                        </div>
                        <p id="error-saldo" class="text-xs text-red-400 mt-1 hidden">
                            ⚠ Melebihi saldo! Maks Rp {{ number_format($sisaBelumSetor, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">No. Slip Bank</label>
                            <input type="text" name="no_referensi" placeholder="Opsional"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Keterangan</label>
                            <input type="text" name="keterangan" placeholder="Opsional"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-setoran').style.display='none'"
                        class="flex-1 bg-white text-gray-400 text-sm py-2 rounded-lg">Batal</button>
                    <button type="submit" id="btn-simpan-setoran" class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium py-2 rounded-lg">
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

    var maxSetor    = {{ $sisaBelumSetor }};
    var maxTunai    = {{ $sisaTunai }};
    var maxTransfer = {{ $sisaTransfer }};

    function toRibuan(num) {
        return parseInt(num || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function dariRibuan(str) {
        return parseInt((str || '0').replace(/\./g, '')) || 0;
    }

    // Input tunai/transfer: format + cap + hitung total otomatis
    function inputKas(input, maxVal, errId) {
        var raw    = input.value.replace(/[^0-9]/g, '');
        var val    = parseInt(raw) || 0;
        var errEl  = document.getElementById(errId);
        var capped = false;

        if (val > maxVal) {
            val    = maxVal;
            capped = true;
        }

        input.value = toRibuan(val);

        if (errEl) {
            if (capped) {
                errEl.classList.remove('hidden');
                input.style.borderColor = '#ef4444';
                setTimeout(function() {
                    errEl.classList.add('hidden');
                    input.style.borderColor = '';
                }, 2500);
            } else {
                errEl.classList.add('hidden');
                input.style.borderColor = '';
            }
        }

        // Hitung total otomatis
        var tunai    = Math.min(dariRibuan(document.querySelector('[name=total_tunai]').value), maxTunai);
        var transfer = Math.min(dariRibuan(document.querySelector('[name=total_transfer]').value), maxTransfer);
        document.getElementById('total-setoran').value = toRibuan(tunai + transfer);
    }

    // Sebelum submit: strip titik ribuan
    document.querySelector('form').addEventListener('submit', function() {
        ['total_tunai','total_transfer','total_setoran'].forEach(function(name) {
            var el = document.querySelector('[name=' + name + ']');
            if (el) el.value = dariRibuan(el.value);
        });
    });
    </script>

</x-simans-layout>
