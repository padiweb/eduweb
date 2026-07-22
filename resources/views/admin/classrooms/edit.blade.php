<x-simans-layout title="Kelola Kelas {{ $classroom->name }}">

    <div class="mb-6">
        <a href="{{ route('admin.classrooms.index') }}"
           class="flex items-center gap-1 text-gray-500 hover:text-gray-900 text-sm mb-2 transition-colors w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $classroom->name }}</h1>
        <p class="text-gray-500 text-sm mt-0.5">
            {{ $classroom->academicYear->label }}
            @if($classroom->major) &middot; {{ $classroom->major->name }} @endif
        </p>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-900/30 border border-red-700/40 text-red-300 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom kiri --}}
        <div class="space-y-5">

            {{-- Edit info kelas --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Info Kelas</h2>
                <form method="POST" action="{{ route('admin.classrooms.update', $classroom->id) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Nama Kelas <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required value="{{ $classroom->name }}"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Tingkat</label>
                        <select name="grade" required
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            @foreach(range(1, 13) as $g)
                                <option value="{{ $g }}" {{ $classroom->grade == $g ? 'selected' : '' }}>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Jurusan</label>
                        <select name="major_id"
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">Tanpa Jurusan</option>
                            @foreach($majors as $m)
                                <option value="{{ $m->id }}" {{ $classroom->major_id == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Wali Kelas</label>
                        <select name="homeroom_teacher_id"
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">Belum ditentukan</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}" {{ $classroom->homeroom_teacher_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Simpan Perubahan
                    </button>
                </form>
            </div>

            {{-- Tambah siswa manual --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Tambah Siswa</h2>
                <form method="POST" action="{{ route('admin.classrooms.assign-student', $classroom->id) }}" class="space-y-3">
                    @csrf
                    <select name="student_id" required
                            class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">Pilih siswa...</option>
                        @foreach($availableStudents->whereNotIn('id', $classroom->students->pluck('id')) as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->nis ?? $s->nisn ?? '-' }})</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Tambah ke Kelas
                    </button>
                </form>
            </div>

            {{-- Import CSV --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-1">Import CSV</h2>
                <p class="text-xs text-gray-500 mb-3">Satu kolom NIS atau NISN per baris.</p>
                <form method="POST" action="{{ route('admin.classrooms.import', $classroom->id) }}"
                      enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none transition-colors file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-gray-100 file:text-gray-400">
                    <button type="submit"
                            class="w-full bg-gray-100 hover:bg-gray-600 text-gray-900 text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Upload & Import
                    </button>
                </form>
            </div>
        </div>

        {{-- Kolom kanan: Daftar siswa --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">Daftar Siswa</h2>
                    <span class="text-xs text-gray-500">{{ $classroom->students->count() }} siswa</span>
                </div>

                @if($classroom->students->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($classroom->students->sortBy('name') as $no => $student)
                            <div class="px-5 py-4" x-data="{ showActions: false }">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-gray-500 w-5 text-right flex-shrink-0">{{ $no + 1 }}</span>
                                    <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0" style="min-width:32px">
                                        <img src="{{ $student->avatarUrl }}" alt="" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $student->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $student->nis ?? '-' }}
                                            @if($student->studentDetail?->gender) &middot; {{ $student->studentDetail->gender }} @endif
                                        </p>
                                    </div>

                                    {{-- Status badge --}}
                                    @php
                                        $statusColors = [
                                            'aktif'   => 'text-blue-600 bg-blue-600/10 border-blue-200',
                                            'alumni'  => 'text-blue-400 bg-blue-500/10 border-blue-500/20',
                                            'keluar'  => 'text-red-400 bg-red-500/10 border-red-500/20',
                                            'pindah'  => 'text-amber-400 bg-amber-500/10 border-amber-500/20',
                                        ];
                                        $statusLabels = ['aktif'=>'Aktif','alumni'=>'Alumni','keluar'=>'Keluar','pindah'=>'Pindah'];
                                        $sc = $statusColors[$student->student_status ?? 'aktif'] ?? $statusColors['aktif'];
                                        $sl = $statusLabels[$student->student_status ?? 'aktif'] ?? 'Aktif';
                                    @endphp
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full border {{ $sc }} flex-shrink-0">
                                        {{ $sl }}
                                    </span>

                                    {{-- Toggle aksi --}}
                                    <button type="button" @click="showActions = !showActions"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors flex-shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"/>
                                        </svg>
                                    </button>

                                    {{-- Hapus dari kelas --}}
                                    <form method="POST"
                                          action="{{ route('admin.classrooms.remove-student', [$classroom->id, $student->id]) }}"
                                          onsubmit="return confirm('Keluarkan {{ addslashes($student->name) }} dari kelas ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg bg-white hover:bg-red-900/40 border border-gray-200 hover:border-red-500/30 text-gray-500 hover:text-red-400 transition-colors flex-shrink-0"
                                                title="Keluarkan dari kelas">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                {{-- Panel aksi individual --}}
                                <div x-show="showActions" x-cloak class="mt-3 ml-8 p-4 bg-white border border-gray-200 rounded-xl space-y-3">

                                    {{-- Pindah kelas --}}
                                    <form method="POST" action="{{ route('admin.promotions.transfer', $student->id) }}"
                                          class="flex items-center gap-2 flex-wrap">
                                        @csrf
                                        <input type="hidden" name="from_classroom_id" value="{{ $classroom->id }}">
                                        <select name="to_classroom_id" required
                                                class="flex-1 bg-white border border-gray-200 text-gray-900 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:border-blue-500 transition-colors min-w-0">
                                            <option value="">Pindah ke kelas...</option>
                                            @foreach($allActiveClassrooms->where('id', '!=', $classroom->id) as $ac)
                                                <option value="{{ $ac->id }}">{{ $ac->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit"
                                                class="text-xs text-blue-400 bg-blue-500/10 border border-blue-500/20 hover:bg-blue-500/20 px-3 py-1.5 rounded-lg transition-colors flex-shrink-0">
                                            Pindah Kelas
                                        </button>
                                    </form>

                                    {{-- Update status --}}
                                    <form method="POST" action="{{ route('admin.promotions.update-status', $student->id) }}"
                                          class="flex items-center gap-2 flex-wrap">
                                        @csrf
                                        <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                                        <select name="student_status" required
                                                class="bg-white border border-gray-200 text-gray-900 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:border-amber-500 transition-colors">
                                            <option value="aktif"  {{ ($student->student_status ?? 'aktif') === 'aktif'  ? 'selected' : '' }}>Aktif</option>
                                            <option value="alumni" {{ ($student->student_status ?? 'aktif') === 'alumni' ? 'selected' : '' }}>Alumni / Lulus</option>
                                            <option value="keluar" {{ ($student->student_status ?? 'aktif') === 'keluar' ? 'selected' : '' }}>Keluar / DO</option>
                                            <option value="pindah" {{ ($student->student_status ?? 'aktif') === 'pindah' ? 'selected' : '' }}>Pindah Sekolah</option>
                                        </select>
                                        <input type="text" name="status_notes"
                                               placeholder="Keterangan (opsional)"
                                               class="flex-1 bg-white border border-gray-200 text-gray-900 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:border-amber-500 transition-colors min-w-0">
                                        <button type="submit"
                                                class="text-xs text-amber-400 bg-amber-500/10 border border-amber-500/20 hover:bg-amber-500/20 px-3 py-1.5 rounded-lg transition-colors flex-shrink-0">
                                            Update Status
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-12 text-center">
                        <p class="text-gray-500 text-sm">Belum ada siswa di kelas ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-simans-layout>
