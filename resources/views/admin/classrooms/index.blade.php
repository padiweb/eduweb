<x-simans-layout title="Manajemen Kelas">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Kelas</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola kelas, wali kelas, dan siswa per kelas</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.majors.index') }}"
               class="text-sm text-gray-500 hover:text-blue-600 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-colors">
                Jurusan
            </a>
            <a href="{{ route('admin.academic-years.index') }}"
               class="text-sm text-gray-500 hover:text-blue-600 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-colors">
                Tahun Ajaran
            </a>
            <button id="btn-tambah-kelas"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Buat Kelas
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-900/30 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter tahun ajaran --}}
    <div class="tab-nav-scroll">
        <a href="{{ route('admin.classrooms.index') }}"
           class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors {{ ! request('year') ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-500 hover:text-gray-700' }}">
            Semua
        </a>
        @foreach($academicYears as $ay)
            <a href="{{ route('admin.classrooms.index', ['year' => $ay->id]) }}"
               class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2
               {{ request('year') == $ay->id ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-500 hover:text-gray-700' }}">
                {{ $ay->label }}
                @if($ay->is_active)
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 inline-block"></span>
                @endif
            </a>
        @endforeach
    </div>

    @if($classrooms->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-blue-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
            </svg>
            <p class="text-gray-500 text-sm">Belum ada kelas{{ request('year') ? ' untuk tahun ajaran ini' : '' }}.</p>
            @if(! $academicYears->count())
                <p class="text-gray-500 text-xs mt-1">Buat <a href="{{ route('admin.academic-years.index') }}" class="text-blue-600 hover:underline">tahun ajaran</a> dulu.</p>
            @endif
        </div>
    @else
        @foreach($classrooms as $ayId => $kelasGroup)
            @php $ay = $kelasGroup->first()->academicYear; @endphp
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-3">
                    <h2 class="text-sm font-bold text-gray-900">{{ $ay->label }}</h2>
                    @if($ay->is_active)
                        <span class="text-xs text-blue-600 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full">Aktif</span>
                    @endif
                    <span class="text-xs text-gray-500">{{ $kelasGroup->count() }} kelas</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($kelasGroup->sortBy('grade') as $classroom)
                        <div class="bg-white border border-gray-200 hover:border-gray-200 rounded-xl p-5 transition-colors">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <p class="text-base font-bold text-gray-900">{{ $classroom->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Kelas {{ $classroom->grade }}
                                        @if($classroom->major) &middot; {{ $classroom->major->code ?? $classroom->major->name }} @endif
                                    </p>
                                </div>
                                <div class="flex gap-1">
                                    <a href="{{ route('admin.classrooms.edit', $classroom->id) }}"
                                       class="w-7 h-7 flex items-center justify-center rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.classrooms.destroy', $classroom->id) }}"
                                          onsubmit="return confirm('Hapus kelas {{ addslashes($classroom->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-white hover:bg-red-900/40 border border-gray-200 hover:border-red-200 text-white hover:text-red-600 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="space-y-1.5 mb-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                    </svg>
                                    <span class="text-xs text-gray-500 truncate">
                                        {{ $classroom->homeroomTeacher?->name ?? 'Belum ada wali kelas' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                    </svg>
                                    <span class="text-xs text-gray-500">{{ $classroom->students->count() }} siswa</span>
                                </div>
                            </div>

                            <a href="{{ route('admin.classrooms.edit', $classroom->id) }}"
                               class="w-full flex items-center justify-center gap-1 text-xs text-blue-600 hover:text-blue-700 bg-blue-600/5 hover:bg-blue-50 border border-blue-200 py-2 rounded-xl transition-colors">
                                Kelola Siswa
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    {{-- Modal Buat Kelas --}}
    <div id="modal-kelas" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
        <div class="bg-white border border-gray-200 rounded-xl w-full max-w-md">
            <div class="flex items-center justify-between p-5 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Buat Kelas Baru</h3>
                <button id="btn-close-modal" class="text-gray-500 hover:text-blue-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.classrooms.store') }}" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Tahun Ajaran <span class="text-red-600">*</span></label>
                    <select name="academic_year_id" required
                            class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">Pilih tahun ajaran...</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $ay->is_active ? 'selected' : '' }}>
                                {{ $ay->label }} {{ $ay->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Tingkat <span class="text-red-600">*</span></label>
                        <select name="grade" required
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            @foreach(range(1, 13) as $g)
                                <option value="{{ $g }}">{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Jurusan (opsional)</label>
                        <select name="major_id"
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">Tanpa Jurusan</option>
                            @foreach($majors as $m)
                                <option value="{{ $m->id }}">{{ $m->code ?? $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Nama Kelas <span class="text-red-600">*</span></label>
                    <input type="text" name="name" required
                           placeholder="cth: XI TKJ 1, X RPL 2, VII A"
                           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Wali Kelas (opsional)</label>
                    <select name="homeroom_teacher_id"
                            class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">Pilih guru...</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" id="btn-cancel-modal"
                            class="flex-1 bg-white hover:bg-gray-50 text-gray-600 text-sm py-2.5 rounded-xl border border-gray-200 transition-colors">Batal</button>
                    <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Buat Kelas</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function() {
        var modal = document.getElementById('modal-kelas');
        function open()  { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        function close() { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        document.getElementById('btn-tambah-kelas')?.addEventListener('click', open);
        document.getElementById('btn-close-modal')?.addEventListener('click', close);
        document.getElementById('btn-cancel-modal')?.addEventListener('click', close);
        modal?.addEventListener('click', function(e) { if (e.target === modal) close(); });
    })();
    </script>

</x-simans-layout>
