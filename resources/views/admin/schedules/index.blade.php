<x-simans-layout title="Jadwal Mengajar">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Jadwal Mengajar</h1>
            <p class="text-gray-500 text-sm mt-1">Atur jadwal guru per kelas — 1 guru bisa mengajar banyak mapel</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.schedules.by-teacher') }}"
               class="text-sm text-gray-500 hover:text-blue-600 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-colors">
                Per Guru
            </a>
            <a href="{{ route('admin.subjects.index') }}"
               class="text-sm text-gray-500 hover:text-blue-600 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-colors">
                Mata Pelajaran
            </a>
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

    {{-- Tab navigasi --}}
    @php $activeTab = request('tab', 'schedule'); @endphp
    <div class="flex gap-1 mb-5 bg-white border border-gray-200 rounded-xl p-1 w-fit">
        <a href="{{ route('admin.schedules.index', ['classroom_id' => request('classroom_id'), 'tab' => 'schedule']) }}"
           class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $activeTab === 'schedule' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-white' }}">
            Jadwal per Kelas
        </a>
        <a href="{{ route('admin.schedules.index', ['tab' => 'mapping']) }}"
           class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $activeTab === 'mapping' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-white' }}">
            Peruntukan Mapel
        </a>
    </div>

    @if($activeTab === 'schedule')
    {{-- ══════════════════════ TAB: JADWAL PER KELAS ══════════════════════ --}}

    {{-- Pilih Kelas --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6">
        <form method="GET" class="flex items-center gap-3 flex-wrap">
            <input type="hidden" name="tab" value="schedule">
            <label class="text-sm text-gray-500 flex-shrink-0">Pilih Kelas:</label>
            <select name="classroom_id" onchange="this.form.submit()"
                    class="flex-1 min-w-0 bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                <option value="">-- Pilih kelas --</option>
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ $classroomId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}{{ $c->major ? ' ('.$c->major->code.')' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($selectedClassroom)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Form tambah jadwal --}}
            <div>
                <div class="bg-white border border-blue-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4">Tambah Jadwal — {{ $selectedClassroom->name }}</h2>
                    <form method="POST" action="{{ route('admin.schedules.store') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="classroom_id" value="{{ $selectedClassroom->id }}">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">Mata Pelajaran <span class="text-red-600">*</span></label>
                            <select name="subject_id" required
                                    class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                <option value="">Pilih mapel...</option>
                                @foreach($subjects->groupBy(fn($s) => $s->group?->name ?? 'Tanpa Kelompok') as $groupName => $mapels)
                                    <optgroup label="{{ $groupName }}">
                                        @foreach($mapels as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">Guru <span class="text-red-600">*</span></label>
                            <select name="teacher_id" required
                                    class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                <option value="">Pilih guru...</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">Hari <span class="text-red-600">*</span></label>
                            <select name="day_of_week" required
                                    class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                @foreach([1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'] as $d => $n)
                                    <option value="{{ $d }}">{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">Mulai <span class="text-red-600">*</span></label>
                                <input type="time" name="start_time" required
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">Selesai <span class="text-red-600">*</span></label>
                                <input type="time" name="end_time" required
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">Ruangan (opsional)</label>
                            <input type="text" name="room" placeholder="cth: Lab TKJ 1, Kelas A"
                                   class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        </div>
                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                            Tambah Jadwal
                        </button>
                    </form>
                </div>
                <div class="mt-4 bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs text-gray-500 leading-relaxed">
                        1 guru bisa mengajar <span class="text-gray-900">banyak mata pelajaran</span> di kelas berbeda.
                        Jadwal ini menjadi acuan guru saat membuat tugas.
                    </p>
                </div>
            </div>

            {{-- Daftar jadwal per hari --}}
            <div class="lg:col-span-2">
                @php $days = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu']; @endphp
                @if($schedules->isEmpty())
                    <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
                        <p class="text-gray-500 text-sm">Belum ada jadwal untuk {{ $selectedClassroom->name }}.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($days as $dayNum => $dayName)
                            @if($schedules->has($dayNum))
                                <div class="tbl-card">
                                    <div class="px-4 py-3 border-b border-gray-200">
                                        <h3 class="text-sm font-bold text-gray-900">{{ $dayName }}</h3>
                                    </div>
                                    <div class="divide-y divide-gray-100">
                                        @foreach($schedules[$dayNum]->sortBy('start_time') as $schedule)
                                            <div class="px-4 py-3" x-data="{ editing: false }">
                                                {{-- Tampilan normal --}}
                                                <div class="flex items-center gap-3" x-show="!editing">
                                                    <div class="text-xs text-gray-500 flex-shrink-0 w-20 text-center leading-relaxed">
                                                        {{ substr($schedule->start_time, 0, 5) }}<br>
                                                        <span class="text-gray-900">↓</span><br>
                                                        {{ substr($schedule->end_time, 0, 5) }}
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $schedule->subject->name }}</p>
                                                        <p class="text-xs text-gray-500 mt-0.5">
                                                            {{ $schedule->teacher->name }}
                                                            @if($schedule->room) &middot; {{ $schedule->room }} @endif
                                                        </p>
                                                    </div>
                                                    <div class="flex items-center gap-1 flex-shrink-0">
                                                        <button type="button" @click="editing=true"
                                                                class="w-7 h-7 flex items-center justify-center rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                                            </svg>
                                                        </button>
                                                        <form method="POST" action="{{ route('admin.schedules.destroy', $schedule->id) }}"
                                                              onsubmit="return confirm('Hapus jadwal ini?')">
                                                            @csrf @method('DELETE')
                                                            <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-white hover:bg-red-900/40 border border-gray-200 hover:border-red-200 text-white hover:text-red-600 transition-colors">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>

                                                {{-- Form edit inline --}}
                                                <form method="POST" action="{{ route('admin.schedules.update', $schedule->id) }}"
                                                      class="space-y-2" x-show="editing" x-cloak>
                                                    @csrf @method('PUT')
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div class="col-span-2">
                                                            <label class="block text-xs text-gray-500 mb-1">Mata Pelajaran</label>
                                                            <select name="subject_id" required
                                                                    class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                                                @foreach($subjects->groupBy(fn($s) => $s->group?->name ?? 'Tanpa Kelompok') as $gName => $mapels)
                                                                    <optgroup label="{{ $gName }}">
                                                                        @foreach($mapels as $s)
                                                                            <option value="{{ $s->id }}" {{ $schedule->subject_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                                                        @endforeach
                                                                    </optgroup>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-span-2">
                                                            <label class="block text-xs text-gray-500 mb-1">Guru</label>
                                                            <select name="teacher_id" required
                                                                    class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                                                @foreach($teachers as $t)
                                                                    <option value="{{ $t->id }}" {{ $schedule->teacher_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-500 mb-1">Hari</label>
                                                            <select name="day_of_week" required
                                                                    class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                                                @foreach([1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'] as $d => $n)
                                                                    <option value="{{ $d }}" {{ $schedule->day_of_week == $d ? 'selected' : '' }}>{{ $n }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-500 mb-1">Ruangan</label>
                                                            <input type="text" name="room" value="{{ $schedule->room }}"
                                                                   placeholder="Ruangan"
                                                                   class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-500 mb-1">Mulai</label>
                                                            <input type="time" name="start_time" value="{{ substr($schedule->start_time,0,5) }}" required
                                                                   class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-500 mb-1">Selesai</label>
                                                            <input type="time" name="end_time" value="{{ substr($schedule->end_time,0,5) }}" required
                                                                   class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                                        </div>
                                                    </div>
                                                    <div class="flex gap-2">
                                                        <button type="submit"
                                                                class="flex-1 text-xs text-blue-600 bg-blue-50 border border-blue-200 hover:bg-blue-50 py-1.5 rounded-lg transition-colors">
                                                            Simpan
                                                        </button>
                                                        <button type="button" @click="editing=false"
                                                                class="flex-1 text-xs text-gray-500 bg-white border border-gray-200 py-1.5 rounded-lg transition-colors">
                                                            Batal
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-blue-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
            </svg>
            <p class="text-gray-500 text-sm">Pilih kelas di atas untuk lihat dan mengatur jadwal.</p>
        </div>
    @endif
    @endif

    @if($activeTab === 'mapping')
    {{-- ══════════════════════ TAB: PERUNTUKAN MAPEL ══════════════════════ --}}
    <div class="tbl-card">
        <div class="px-5 py-4 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-900">Mapel → Kelas yang Mengajarkan</h2>
            <p class="text-xs text-gray-500 mt-0.5">Daftar setiap mata pelajaran dan kelas aktif yang memiliki jadwal mapel tersebut</p>
        </div>

        @if($subjectMapping->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-500 text-sm">Belum ada jadwal yang dibuat.</p>
                <p class="text-gray-500 text-xs mt-1">Buat jadwal di tab "Jadwal per Kelas" dulu.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($subjectMapping as $item)
                    <div class="px-5 py-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $item['subject']->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $item['subject']->group?->name ?? 'Tanpa kelompok' }}
                                    @if($item['subject']->code) &middot; {{ $item['subject']->code }} @endif
                                </p>
                            </div>
                            <span class="text-xs text-gray-500 flex-shrink-0">{{ $item['classes']->count() }} kelas</span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach($item['classes'] as $cls)
                                <a href="{{ route('admin.schedules.index', ['classroom_id' => $cls->id, 'tab' => 'schedule']) }}"
                                   class="text-xs text-blue-400 bg-blue-50 border border-blue-200 hover:bg-blue-50 px-2.5 py-1 rounded-lg transition-colors">
                                    {{ $cls->name }}
                                    @if($cls->major) ({{ $cls->major->code ?? $cls->major->name }}) @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif

</x-simans-layout>
