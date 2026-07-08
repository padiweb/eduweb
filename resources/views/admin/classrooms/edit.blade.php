<x-simans-layout title="Kelola Kelas {{ $classroom->name }}">

    <div class="mb-6">
        <a href="{{ route('admin.classrooms.index') }}"
           class="flex items-center gap-1 text-gray-400 hover:text-white text-sm mb-2 transition-colors w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-white">{{ $classroom->name }}</h1>
        <p class="text-gray-400 text-sm mt-0.5">
            {{ $classroom->academicYear->label }}
            @if($classroom->major) &middot; {{ $classroom->major->name }} @endif
        </p>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-300 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-900/30 border border-red-700/40 text-red-300 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom kiri: Edit info kelas --}}
        <div class="space-y-5">

            {{-- Edit info kelas --}}
            <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-white mb-4">Info Kelas</h2>
                <form method="POST" action="{{ route('admin.classrooms.update', $classroom->id) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Nama Kelas <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required value="{{ $classroom->name }}"
                               class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Tingkat</label>
                        <select name="grade" required
                                class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            @foreach(range(1, 13) as $g)
                                <option value="{{ $g }}" {{ $classroom->grade == $g ? 'selected' : '' }}>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Jurusan</label>
                        <select name="major_id"
                                class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <option value="">Tanpa Jurusan</option>
                            @foreach($majors as $m)
                                <option value="{{ $m->id }}" {{ $classroom->major_id == $m->id ? 'selected' : '' }}>
                                    {{ $m->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Wali Kelas</label>
                        <select name="homeroom_teacher_id"
                                class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <option value="">Belum ditentukan</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}" {{ $classroom->homeroom_teacher_id == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Simpan Perubahan
                    </button>
                </form>
            </div>

            {{-- Tambah siswa manual --}}
            <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-white mb-4">Tambah Siswa</h2>
                <form method="POST" action="{{ route('admin.classrooms.assign-student', $classroom->id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Pilih Siswa</label>
                        <select name="student_id" required
                                class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <option value="">Pilih siswa...</option>
                            @foreach($availableStudents->whereNotIn('id', $classroom->students->pluck('id')) as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->name }} ({{ $s->nis ?? $s->nisn ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Tambah ke Kelas
                    </button>
                </form>
            </div>

            {{-- Import CSV --}}
            <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-white mb-1">Import CSV</h2>
                <p class="text-xs text-gray-500 mb-3">Format: satu kolom NIS atau NISN per baris.</p>
                <form method="POST" action="{{ route('admin.classrooms.import', $classroom->id) }}"
                      enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2 text-sm focus:outline-none transition-colors file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-gray-700 file:text-gray-300">
                    <button type="submit"
                            class="w-full bg-gray-700 hover:bg-gray-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Upload & Import
                    </button>
                </form>
                <div class="mt-3 bg-gray-800 rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-500 font-mono">NIS (header opsional)</p>
                    <p class="text-xs text-gray-400 font-mono">12345</p>
                    <p class="text-xs text-gray-400 font-mono">12346</p>
                    <p class="text-xs text-gray-400 font-mono">12347</p>
                </div>
            </div>
        </div>

        {{-- Kolom kanan: Daftar siswa --}}
        <div class="lg:col-span-2">
            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Daftar Siswa</h2>
                    <span class="text-xs text-gray-500">{{ $classroom->students->count() }} siswa</span>
                </div>

                @if($classroom->students->count() > 0)
                    <div class="divide-y divide-white/5">
                        @foreach($classroom->students->sortBy('name') as $no => $student)
                            <div class="flex items-center gap-3 px-5 py-3.5">
                                <span class="text-xs text-gray-600 w-6 text-right flex-shrink-0">{{ $no + 1 }}</span>
                                <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0" style="min-width:32px">
                                    <img src="{{ $student->avatarUrl }}" alt="" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate">{{ $student->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        NIS: {{ $student->nis ?? '-' }}
                                        @if($student->nisn) &middot; NISN: {{ $student->nisn }} @endif
                                        @if($student->studentDetail?->gender)
                                            &middot; {{ $student->studentDetail->gender === 'L' ? 'L' : 'P' }}
                                        @endif
                                    </p>
                                </div>
                                <form method="POST"
                                      action="{{ route('admin.classrooms.remove-student', [$classroom->id, $student->id]) }}"
                                      onsubmit="return confirm('Keluarkan {{ addslashes($student->name) }} dari kelas ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-800 hover:bg-red-900/40 border border-white/10 hover:border-red-500/30 text-gray-400 hover:text-red-400 transition-colors flex-shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-12 text-center">
                        <p class="text-gray-500 text-sm">Belum ada siswa di kelas ini.</p>
                        <p class="text-gray-600 text-xs mt-1">Tambahkan via form di sebelah kiri atau upload CSV.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-simans-layout>
