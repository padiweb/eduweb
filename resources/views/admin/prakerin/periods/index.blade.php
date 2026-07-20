<x-simans-layout title="Manajemen Prakerin">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Manajemen Prakerin</h1>
            <p class="text-gray-400 text-sm mt-1">Kelola periode, DU/DI, dan penempatan siswa</p>
        </div>
        <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
                class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Periode
        </button>
    </div>

    {{-- Sub-nav Prakerin --}}
    <div class="flex gap-2 mb-6 flex-wrap">
        <a href="{{ route('admin.prakerin.periods.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium bg-emerald-500 text-white">
            Periode
        </a>
        <a href="{{ route('admin.prakerin.locations.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">
            DU/DI
        </a>
        <a href="{{ route('admin.prakerin.placements.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">
            Penempatan Siswa
        </a>
        <a href="{{ route('admin.prakerin.recap.absensi') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">
            Rekap Absensi
        </a>
        <a href="{{ route('admin.prakerin.recap.jurnal') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">
            Rekap Jurnal
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
    @endif

    @if ($periods->isEmpty())
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-12 text-center">
            <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            <p class="text-gray-400">Belum ada periode prakerin.</p>
            <p class="text-gray-600 text-sm mt-1">Buat periode terlebih dahulu sebelum mengatur DU/DI dan penempatan siswa.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($periods as $period)
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-white font-semibold">{{ $period->name }}</h3>
                                @php
                                    $statusColor = match($period->status_label) {
                                        'Berlangsung' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        'Belum Mulai' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'Selesai'     => 'bg-gray-700 text-gray-400 border-white/10',
                                        default       => 'bg-gray-700 text-gray-400 border-white/10',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold border {{ $statusColor }}">
                                    {{ $period->status_label }}
                                </span>
                            </div>
                            <p class="text-gray-400 text-sm">
                                {{ $period->academicYear->label ?? '—' }} ·
                                {{ $period->start_date->format('d M Y') }} – {{ $period->end_date->format('d M Y') }}
                                ({{ $period->start_date->diffInDays($period->end_date) + 1 }} hari)
                            </p>
                            @if ($period->description)
                                <p class="text-gray-500 text-xs mt-1">{{ $period->description }}</p>
                            @endif
                            <div class="flex items-center gap-4 mt-3">
                                <span class="text-xs text-gray-500">
                                    <span class="text-white font-medium">{{ $period->locations()->count() }}</span> DU/DI
                                </span>
                                <span class="text-xs text-gray-500">
                                    <span class="text-white font-medium">{{ $period->placements()->count() }}</span> Siswa
                                </span>
                                <span class="text-xs text-gray-500">
                                    <span class="text-white font-medium">{{ $period->coordinators->count() }}</span> Koordinator
                                </span>
                            </div>
                            {{-- Koordinator badges --}}
                            @if ($period->coordinators->count() > 0)
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach ($period->coordinators as $coord)
                                        <span class="px-2 py-0.5 bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs rounded-lg">
                                            {{ $coord->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button onclick="openKoordinator({{ $period->id }}, {{ json_encode($period->coordinators->pluck('id')) }})"
                                    class="px-3 py-1.5 bg-blue-600/10 hover:bg-blue-600/20 border border-blue-500/20 text-blue-400 text-xs rounded-lg transition-colors">
                                Koordinator
                            </button>
                            <button onclick="openEdit({{ $period->id }}, '{{ addslashes($period->name) }}', '{{ $period->start_date->format('Y-m-d') }}', '{{ $period->end_date->format('Y-m-d') }}', '{{ addslashes($period->description ?? '') }}', {{ $period->is_active ? 'true' : 'false' }}, {{ json_encode($period->coordinators->pluck('id')) }})"
                                    class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-300 text-xs rounded-lg transition-colors">
                                Edit
                            </button>
                            @if ($period->placements()->count() === 0)
                                <form action="{{ route('admin.prakerin.periods.destroy', $period) }}" method="POST"
                                      onsubmit="return confirm('Hapus periode ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 bg-red-600/10 hover:bg-red-600/20 border border-red-500/20 text-red-400 text-xs rounded-lg transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal Tambah --}}
    <div id="modal-tambah" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-gray-950 border border-white/10 rounded-2xl p-6 w-full max-w-md">
            <h2 class="text-white font-semibold mb-4">Tambah Periode Prakerin</h2>
            <form action="{{ route('admin.prakerin.periods.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" required class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                        <option value="">— Pilih —</option>
                        @foreach (\App\Models\AcademicYear::where('school_id', auth()->user()->school_id)->orderByDesc('start_date')->get() as $y)
                            <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>{{ $y->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Nama Periode</label>
                    <input type="text" name="name" required placeholder="cth: Prakerin Semester Genap 2025/2026"
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 placeholder-gray-600">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" required class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" required class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Keterangan (opsional)</label>
                    <textarea name="description" rows="2" placeholder="Catatan atau keterangan periode"
                              class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 resize-none placeholder-gray-600"></textarea>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-2">Koordinator Prakerin (bisa lebih dari 1)</label>
                    <div class="space-y-1 max-h-36 overflow-y-auto pr-1">
                        @foreach ($teachers as $t)
                            <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/5">
                                <input type="checkbox" name="coordinator_ids[]" value="{{ $t->id }}"
                                       class="w-4 h-4 rounded accent-blue-500">
                                <span class="text-sm text-gray-300">{{ $t->name }}</span>
                                <span class="text-xs text-gray-600">{{ ucfirst(str_replace('_', ' ', $t->role)) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                            class="flex-1 py-2.5 bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-300 text-sm rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-gray-950 border border-white/10 rounded-2xl p-6 w-full max-w-md">
            <h2 class="text-white font-semibold mb-4">Edit Periode</h2>
            <form id="form-edit" method="POST" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Nama Periode</label>
                    <input type="text" id="edit-name" name="name" required
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Tanggal Mulai</label>
                        <input type="date" id="edit-start" name="start_date" required class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Tanggal Selesai</label>
                        <input type="date" id="edit-end" name="end_date" required class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Keterangan</label>
                    <textarea id="edit-desc" name="description" rows="2" class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 resize-none"></textarea>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="edit-active" name="is_active" value="1" class="w-4 h-4 rounded accent-emerald-500">
                    <span class="text-sm text-gray-300">Aktif</span>
                </label>
                <div>
                    <label class="block text-xs text-gray-400 mb-2">Koordinator Prakerin</label>
                    <div id="edit-coordinators" class="space-y-1 max-h-36 overflow-y-auto pr-1">
                        @foreach ($teachers as $t)
                            <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/5">
                                <input type="checkbox" name="coordinator_ids[]" value="{{ $t->id }}"
                                       class="edit-coord-cb w-4 h-4 rounded accent-blue-500">
                                <span class="text-sm text-gray-300">{{ $t->name }}</span>
                                <span class="text-xs text-gray-600">{{ ucfirst(str_replace('_', ' ', $t->role)) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                            class="flex-1 py-2.5 bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-300 text-sm rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Koordinator (quick sync tanpa edit periode) --}}
    <div id="modal-koordinator" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-gray-950 border border-white/10 rounded-2xl p-6 w-full max-w-sm">
            <h2 class="text-white font-semibold mb-1">Koordinator Prakerin</h2>
            <p class="text-gray-500 text-xs mb-4">Centang guru yang bertugas sebagai koordinator di periode ini</p>
            <form id="form-koordinator" method="POST" class="space-y-2">
                @csrf
                <div class="space-y-1 max-h-64 overflow-y-auto pr-1">
                    @foreach ($teachers as $t)
                        <label class="flex items-center gap-2 cursor-pointer p-2.5 rounded-xl hover:bg-white/5 transition-colors">
                            <input type="checkbox" name="coordinator_ids[]" value="{{ $t->id }}"
                                   class="koordinator-cb w-4 h-4 rounded accent-blue-500">
                            <div>
                                <span class="text-sm text-gray-200">{{ $t->name }}</span>
                                <span class="ml-1 text-xs text-gray-600">{{ ucfirst(str_replace('_', ' ', $t->role)) }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                <div class="flex gap-3 pt-3 border-t border-white/5">
                    <button type="button" onclick="document.getElementById('modal-koordinator').classList.add('hidden')"
                            class="flex-1 py-2.5 bg-gray-800 border border-white/10 text-gray-300 text-sm rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEdit(id, name, start, end, desc, active, coordIds) {
            document.getElementById('form-edit').action = '/admin/prakerin/periods/' + id;
            document.getElementById('edit-name').value  = name;
            document.getElementById('edit-start').value = start;
            document.getElementById('edit-end').value   = end;
            document.getElementById('edit-desc').value  = desc;
            document.getElementById('edit-active').checked = active;
            // Set koordinator checkboxes
            document.querySelectorAll('.edit-coord-cb').forEach(cb => {
                cb.checked = (coordIds || []).includes(parseInt(cb.value));
            });
            document.getElementById('modal-edit').classList.remove('hidden');
        }

        function openKoordinator(periodId, coordIds) {
            document.getElementById('form-koordinator').action =
                '/admin/prakerin/periods/' + periodId + '/coordinators';
            document.querySelectorAll('.koordinator-cb').forEach(cb => {
                cb.checked = (coordIds || []).includes(parseInt(cb.value));
            });
            document.getElementById('modal-koordinator').classList.remove('hidden');
        }

        ['modal-tambah','modal-edit','modal-koordinator'].forEach(id => {
            document.getElementById(id).addEventListener('click', function(e) {
                if (e.target === this) this.classList.add('hidden');
            });
        });
    </script>

</x-simans-layout>
