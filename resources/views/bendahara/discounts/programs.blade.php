<x-simans-layout title="Program Beasiswa">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Program Beasiswa & Keringanan</h1>
            <p class="text-gray-500 text-sm mt-0.5">Buat program beasiswa, tambah siswa massal, atur nominal per siswa</p>
        </div>
        <button onclick="document.getElementById('modal-add').style.display='flex'"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buat Program
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-600 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg px-4 py-3 mb-4">{{ $errors->first() }}</div>
    @endif

    {{-- Info jenis beasiswa --}}
    <div class="grid grid-cols-2 gap-3 mb-5">
        <div class="bg-blue-500/5 border border-blue-200 rounded-xl px-4 py-3 text-xs text-blue-300">
            <p class="font-semibold mb-1">Beasiswa Dana (Cash)</p>
            <p>Uang beasiswa diterima sekolah → masuk pemasukan kas. Contoh: PIP, beasiswa perusahaan.</p>
        </div>
        <div class="bg-blue-500/5 border border-blue-200 rounded-xl px-4 py-3 text-xs text-blue-500">
            <p class="font-semibold mb-1">Beasiswa Potongan (Waiver)</p>
            <p>Tagihan dikurangi → tidak masuk pemasukan. Contoh: keringanan sekolah, beasiswa prestasi.</p>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($programs as $program)
        @php
            $appliedCount = $appliedCounts[$program->id] ?? 0;
            $memberCount  = $program->members_count;
            $canDelete    = $memberCount === 0;
        @endphp
        <div class="tbl-card">
            <div class="px-5 py-4 flex items-center gap-4">
                {{-- Badge kode --}}
                @php $stype = $program->scholarship_type ?? 'cash'; @endphp
                <div class="w-14 h-14 rounded-xl flex-shrink-0 flex flex-col items-center justify-center gap-0.5
                    {{ $stype === 'waiver'
                        ? 'bg-blue-50 border border-blue-200'
                        : 'bg-blue-50 border border-blue-200' }}">
                    <span class="text-xs font-bold {{ $stype === 'waiver' ? 'text-blue-600' : 'text-blue-400' }}">
                        {{ $program->code ?? strtoupper(substr($program->name, 0, 3)) }}
                    </span>
                    <span class="text-xs {{ $stype === 'waiver' ? 'text-blue-500' : 'text-blue-300' }}">
                        {{ $stype === 'waiver' ? 'Potong' : 'Dana' }}
                    </span>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold text-gray-900">{{ $program->name }}</p>
                        @if(!$program->is_active)
                            <span class="text-xs bg-white text-gray-500 px-2 py-0.5 rounded-full">Nonaktif</span>
                        @endif
                        @if($memberCount === 0)
                            <span class="text-xs bg-white text-gray-500 px-2 py-0.5 rounded-full">Belum ada siswa</span>
                        @elseif($appliedCount >= $memberCount)
                            <span class="text-xs bg-green-50 text-green-600 border border-green-200 px-2 py-0.5 rounded-full">
                                Sudah diterapkan ({{ $appliedCount }} siswa)
                            </span>
                        @elseif($appliedCount > 0)
                            <span class="text-xs bg-amber-50 text-amber-600 border border-amber-200 px-2 py-0.5 rounded-full">
                                Sebagian ({{ $appliedCount }}/{{ $memberCount }})
                            </span>
                        @else
                            <span class="text-xs bg-red-50 text-red-600 border border-red-200 px-2 py-0.5 rounded-full">
                                Belum diterapkan
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $program->academicYear->name ?? '-' }} Sem {{ $program->academicYear->semester ?? '' }} ·
                        {{ $program->paymentType?->name ?? 'Semua jenis' }} ·
                        @if($program->discount_type === 'percent')
                            {{ $program->default_value }}% default
                        @else
                            Rp {{ number_format($program->default_value, 0, ',', '.') }} default
                        @endif
                        · <span class="text-blue-600">{{ $memberCount }} siswa</span>
                        ·
                        @if(($program->scholarship_type ?? 'cash') === 'waiver')
                            <span class="text-blue-600 font-medium">Potongan tagihan</span>
                        @else
                            <span class="text-blue-400 font-medium">Dana masuk kas</span>
                        @endif
                    </p>
                    @if($program->description)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $program->description }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    {{-- Terapkan --}}
                    @if($memberCount > 0)
                    <form method="POST" action="{{ route('bendahara.discount-programs.apply', $program) }}">
                        @csrf
                        <button type="submit"
                            onclick="return confirm('Terapkan {{ addslashes($program->name) }} ke {{ $memberCount }} siswa?')"
                            class="text-xs bg-green-50 hover:bg-green-50 border border-green-200 text-green-600 px-3 py-1.5 rounded-lg transition-colors">
                            Terapkan
                        </button>
                    </form>
                    @endif

                    {{-- Kelola siswa --}}
                    <a href="{{ route('bendahara.discount-programs.members', $program) }}"
                        class="text-xs bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 px-3 py-1.5 rounded-lg transition-colors">
                        Kelola Siswa ({{ $memberCount }})
                    </a>

                    {{-- Edit nominal --}}
                    <button type="button"
                        onclick="openEdit({{ $program->id }}, '{{ addslashes($program->name) }}', {{ $program->default_value }}, '{{ $program->discount_type }}', '{{ $program->scholarship_type ?? 'cash' }}')"
                        class="text-xs text-gray-500 hover:text-blue-600 transition-colors px-2 py-1.5">
                        Edit
                    </button>

                    {{-- Toggle aktif --}}
                    <form method="POST" action="{{ route('bendahara.discount-programs.toggle', $program) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                            class="text-xs {{ $program->is_active ? 'text-green-600' : 'text-gray-500 hover:text-green-600' }} transition-colors px-2 py-1.5">
                            {{ $program->is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </form>

                    {{-- Hapus (hanya jika belum ada siswa) --}}
                    @if($canDelete)
                    <form method="POST" action="{{ route('bendahara.discount-programs.destroy', $program) }}"
                        onsubmit="return confirm('Hapus program {{ addslashes($program->name) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-gray-500 hover:text-red-600 transition-colors px-1 py-1.5">
                            Hapus
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white border border-gray-200 rounded-xl px-5 py-12 text-center">
            <p class="text-gray-500">Belum ada program beasiswa.</p>
        </div>
        @endforelse
    </div>

    {{ $programs->links() }}

    {{-- MODAL Buat Program --}}
    <div id="modal-add" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
        <div class="bg-white border border-gray-200 rounded-xl w-full max-w-md p-6 overflow-y-auto max-h-screen">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-900 font-semibold">Buat Program Beasiswa</h3>
                <button onclick="document.getElementById('modal-add').style.display='none'" class="text-gray-500 hover:text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('bendahara.discount-programs.store') }}">
                @csrf
                <div class="space-y-3">
                    {{-- Jenis beasiswa - paling atas agar jelas --}}
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Jenis Beasiswa *</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:border-blue-200">
                                <input type="radio" name="scholarship_type" value="cash" checked class="text-blue-500">
                                <div>
                                    <p class="text-xs font-medium text-gray-900">Dana (Cash)</p>
                                    <p class="text-xs text-gray-500">Masuk pemasukan kas</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:border-blue-300">
                                <input type="radio" name="scholarship_type" value="waiver" class="text-blue-600">
                                <div>
                                    <p class="text-xs font-medium text-gray-900">Potongan (Waiver)</p>
                                    <p class="text-xs text-gray-500">Tidak masuk pemasukan</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500 mb-1 block">Nama Program *</label>
                            <input type="text" name="name" required placeholder="PIP 2025/2026"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Kode</label>
                            <input type="text" name="code" placeholder="PIP"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Tahun Ajaran *</label>
                        <select name="academic_year_id" required
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            @foreach($academicYears as $y)
                                <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>
                                    {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Berlaku untuk jenis tagihan</label>
                        <select name="payment_type_id"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            <option value="">Semua jenis tagihan</option>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Jenis Nilai *</label>
                            <select name="discount_type" id="dtype" required
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                                <option value="percent">Persen (%)</option>
                                <option value="fixed">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Nilai Default *</label>
                            <div class="relative">
                                <input type="number" name="default_value" required min="0" placeholder="50"
                                    class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 pr-8 focus:border-blue-500 focus:outline-none">
                                <span id="dtype-label" class="absolute right-3 top-2 text-xs text-gray-500">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Mulai Berlaku *</label>
                            <input type="date" name="valid_from" required value="{{ date('Y-m-01') }}"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Berakhir</label>
                            <input type="date" name="valid_until"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Keterangan</label>
                        <input type="text" name="description" placeholder="Keterangan program"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-add').style.display='none'"
                        class="flex-1 bg-white text-gray-600 text-sm py-2 rounded-lg">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg">Buat</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL Edit Program --}}
    <div id="modal-edit" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
        <div class="bg-white border border-gray-200 rounded-xl w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-900 font-semibold">Edit Program</h3>
                <button onclick="document.getElementById('modal-edit').style.display='none'" class="text-gray-500 hover:text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="form-edit" method="POST" action="">
                @csrf @method('PUT')
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Nama Program</label>
                        <input type="text" name="name" id="edit-name" required
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Nilai Default</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="default_value" id="edit-value" required min="0"
                                class="flex-1 bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            <span id="edit-unit" class="text-xs text-gray-500 w-8">%</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Jenis Beasiswa</label>
                        <select name="scholarship_type" id="edit-stype"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            <option value="cash">Dana (Cash) — masuk pemasukan</option>
                            <option value="waiver">Potongan (Waiver) — tidak masuk pemasukan</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-edit').style.display='none'"
                        class="flex-1 bg-white text-gray-600 text-sm py-2 rounded-lg">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('modal-add').addEventListener('click', function(e) { if(e.target===this) this.style.display='none'; });
    document.getElementById('modal-edit').addEventListener('click', function(e) { if(e.target===this) this.style.display='none'; });
    document.getElementById('dtype').addEventListener('change', function() {
        document.getElementById('dtype-label').textContent = this.value === 'percent' ? '%' : 'Rp';
    });

    function openEdit(id, name, value, discountType, scholarshipType) {
        document.getElementById('form-edit').action = '/bendahara/discount-programs/' + id;
        document.getElementById('edit-name').value  = name;
        document.getElementById('edit-value').value = value;
        document.getElementById('edit-unit').textContent = discountType === 'percent' ? '%' : 'Rp';
        document.getElementById('edit-stype').value = scholarshipType;
        document.getElementById('modal-edit').style.display = 'flex';
    }
    </script>

</x-simans-layout>
