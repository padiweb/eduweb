<x-simans-layout title="Program Beasiswa">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-white">Program Beasiswa & Keringanan</h1>
            <p class="text-gray-400 text-sm mt-0.5">Buat program beasiswa, tambah siswa massal, atur nominal per siswa</p>
        </div>
        <button onclick="document.getElementById('modal-add').style.display='flex'"
            class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buat Program
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-blue-500/5 border border-blue-500/15 rounded-xl px-4 py-3 mb-5 text-xs text-blue-300">
        <strong>Cara kerja:</strong> Buat program (misal "PIP 2025/2026") →
        klik <strong>Kelola Siswa</strong> untuk tambah siswa massal dan atur override nominal →
        klik <strong>Terapkan</strong> agar beasiswa aktif di tagihan siswa.
    </div>

    <div class="space-y-3">
        @forelse($programs as $program)
        @php
            $appliedCount  = $appliedCounts[$program->id] ?? 0;
            $memberCount   = $program->members_count;
        @endphp
        <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
            <div class="px-5 py-4 flex items-center gap-4">
                {{-- Badge kode --}}
                <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center text-xs font-bold
                    {{ $program->is_active ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' : 'bg-gray-800 text-gray-600 border border-white/5' }}">
                    {{ $program->code ?? substr($program->name, 0, 3) }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold text-white">{{ $program->name }}</p>

                        {{-- Status aktif/nonaktif --}}
                        @if(!$program->is_active)
                            <span class="text-xs bg-gray-800 text-gray-500 px-2 py-0.5 rounded-full">Nonaktif</span>
                        @endif

                        {{-- Status penerapan --}}
                        @if($memberCount === 0)
                            <span class="text-xs bg-gray-800 text-gray-500 px-2 py-0.5 rounded-full">Belum ada siswa</span>
                        @elseif($appliedCount >= $memberCount)
                            <span class="text-xs bg-green-500/10 text-green-400 border border-green-500/20 px-2 py-0.5 rounded-full">
                                Sudah diterapkan ({{ $appliedCount }} siswa)
                            </span>
                        @elseif($appliedCount > 0)
                            <span class="text-xs bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-0.5 rounded-full">
                                Sebagian diterapkan ({{ $appliedCount }}/{{ $memberCount }})
                            </span>
                        @else
                            <span class="text-xs bg-red-500/10 text-red-400 border border-red-500/20 px-2 py-0.5 rounded-full">
                                Belum diterapkan
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $program->academicYear->name ?? '-' }} Sem {{ $program->academicYear->semester ?? '' }} ·
                        {{ $program->paymentType?->name ?? 'Semua jenis tagihan' }} ·
                        @if($program->discount_type === 'percent')
                            {{ $program->default_value }}% default
                        @else
                            Rp {{ number_format($program->default_value, 0, ',', '.') }} default
                        @endif
                        · <span class="text-purple-400">{{ $memberCount }} siswa</span>
                    </p>

                    @if($program->description)
                        <p class="text-xs text-gray-600 mt-0.5">{{ $program->description }}</p>
                    @endif
                </div>

                {{-- Aksi --}}
                <div class="flex items-center gap-2 shrink-0">
                    <form method="POST" action="{{ route('bendahara.discount-programs.apply', $program) }}">
                        @csrf
                        <button type="submit"
                            onclick="return confirm('Terapkan beasiswa {{ addslashes($program->name) }} ke {{ $memberCount }} siswa?')"
                            class="text-xs bg-green-600/10 hover:bg-green-600/20 border border-green-500/20 text-green-400 px-3 py-1.5 rounded-lg transition-colors">
                            Terapkan
                        </button>
                    </form>
                    <a href="{{ route('bendahara.discount-programs.members', $program) }}"
                        class="text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 border border-white/10 px-3 py-1.5 rounded-lg transition-colors">
                        Kelola Siswa ({{ $memberCount }})
                    </a>
                    <form method="POST" action="{{ route('bendahara.discount-programs.toggle', $program) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                            class="text-xs {{ $program->is_active ? 'text-green-400 hover:text-gray-400' : 'text-gray-500 hover:text-green-400' }} transition-colors px-2 py-1.5">
                            {{ $program->is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-gray-900 border border-white/5 rounded-xl px-5 py-12 text-center">
            <p class="text-gray-500">Belum ada program beasiswa.</p>
            <p class="text-gray-600 text-xs mt-1">Klik Buat Program untuk memulai.</p>
        </div>
        @endforelse
    </div>

    {{ $programs->links() }}

    {{-- MODAL Buat Program --}}
    <div id="modal-add" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-white font-semibold">Buat Program Beasiswa</h3>
                <button onclick="document.getElementById('modal-add').style.display='none'"
                    class="text-gray-600 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('bendahara.discount-programs.store') }}">
                @csrf
                <div class="space-y-3">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="text-xs text-gray-400 mb-1 block">Nama Program *</label>
                            <input type="text" name="name" required placeholder="PIP 2025/2026"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Kode</label>
                            <input type="text" name="code" placeholder="PIP"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Tahun Ajaran *</label>
                        <select name="academic_year_id" required
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            @foreach($academicYears as $y)
                                <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>
                                    {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Berlaku untuk jenis tagihan</label>
                        <select name="payment_type_id"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            <option value="">Semua jenis tagihan</option>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Jenis Diskon *</label>
                            <select name="discount_type" id="dtype" required
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                <option value="percent">Persen (%)</option>
                                <option value="fixed">Nominal tetap (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Nilai Default *</label>
                            <div class="relative">
                                <input type="number" name="default_value" required min="0" placeholder="50"
                                    class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 pr-8 focus:border-purple-500 focus:outline-none">
                                <span id="dtype-label" class="absolute right-3 top-2 text-xs text-gray-500">%</span>
                            </div>
                            <p id="dtype-hint" class="text-xs text-gray-600 mt-1">Contoh: 50 = diskon 50%</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Mulai Berlaku *</label>
                            <input type="date" name="valid_from" required value="{{ date('Y-m-01') }}"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Berakhir (opsional)</label>
                            <input type="date" name="valid_until"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Keterangan</label>
                        <input type="text" name="description" placeholder="Beasiswa pemerintah untuk siswa kurang mampu"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-add').style.display='none'"
                        class="flex-1 bg-gray-800 text-gray-300 text-sm py-2 rounded-lg">Batal</button>
                    <button type="submit"
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2 rounded-lg">
                        Buat Program
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('modal-add').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
    document.getElementById('dtype').addEventListener('change', function() {
        const isPercent = this.value === 'percent';
        document.getElementById('dtype-label').textContent = isPercent ? '%' : 'Rp';
        document.getElementById('dtype-hint').textContent  = isPercent
            ? 'Contoh: 50 = diskon 50%'
            : 'Contoh: 150000 = diskon Rp 150.000';
    });
    </script>

</x-simans-layout>
