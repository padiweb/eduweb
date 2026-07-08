<x-simans-layout title="Jadwal Mengajar">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Jadwal Mengajar</h1>
            <p class="text-gray-400 text-sm mt-1">Atur jadwal guru per kelas — 1 guru bisa mengajar banyak mapel</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.schedules.by-teacher') }}"
               class="text-sm text-gray-400 hover:text-white bg-gray-800 border border-white/10 px-4 py-2 rounded-xl transition-colors">
                Per Guru
            </a>
            <a href="{{ route('admin.subjects.index') }}"
               class="text-sm text-gray-400 hover:text-white bg-gray-800 border border-white/10 px-4 py-2 rounded-xl transition-colors">
                Mata Pelajaran
            </a>
        </div>
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

    {{-- Pilih Kelas --}}
    <div class="bg-gray-900 border border-white/5 rounded-xl p-4 mb-6">
        <form method="GET" class="flex items-center gap-3 flex-wrap">
            <label class="text-sm text-gray-400 flex-shrink-0">Pilih Kelas:</label>
            <select name="classroom_id" onchange="this.form.submit()"
                    class="flex-1 min-w-0 bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                <option value="">-- Pilih kelas untuk lihat/atur jadwal --</option>
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ $classroomId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} {{ $c->major ? '('.$c->major->code.')' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($selectedClassroom)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Form tambah jadwal --}}
            <div>
                <div class="bg-gray-900 border border-emerald-500/20 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-4">
                        Tambah Jadwal — {{ $selectedClassroom->name }}
                    </h2>
                    <form method="POST" action="{{ route('admin.schedules.store') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="classroom_id" value="{{ $selectedClassroom->id }}">

                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Mata Pelajaran <span class="text-red-400">*</span></label>
                            <select name="subject_id" required
                                    class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                <option value="">Pilih mapel...</option>
                                @foreach($subjects->groupBy('category') as $cat => $mapels)
                                    <optgroup label="Kelompok {{ $cat }}">
                                        @foreach($mapels as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Guru <span class="text-red-400">*</span></label>
                            <select name="teacher_id" required
                                    class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                <option value="">Pilih guru...</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Hari <span class="text-red-400">*</span></label>
                            <select name="day_of_week" required
                                    class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                @foreach([1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'] as $d => $name)
                                    <option value="{{ $d }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Mulai <span class="text-red-400">*</span></label>
                                <input type="time" name="start_time" required
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Selesai <span class="text-red-400">*</span></label>
                                <input type="time" name="end_time" required
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Ruangan (opsional)</label>
                            <input type="text" name="room"
                                   placeholder="cth: Lab Komputer 1, Kelas A"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        </div>

                        <button type="submit"
                                class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                            Tambah Jadwal
                        </button>
                    </form>
                </div>

                <div class="mt-4 bg-gray-900 border border-white/5 rounded-xl p-4">
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Satu guru bisa mengajar <span class="text-white">banyak mata pelajaran</span> di kelas yang berbeda-beda.
                        Jadwal ini digunakan sebagai acuan saat guru membuat tugas.
                    </p>
                </div>
            </div>

            {{-- Tabel jadwal per hari --}}
            <div class="lg:col-span-2">
                @php
                    $days = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'];
                    $dayColors = [
                        1=>'blue',2=>'emerald',3=>'amber',4=>'purple',5=>'rose',6=>'orange'
                    ];
                @endphp

                @if($schedules->isEmpty())
                    <div class="bg-gray-900 border border-white/5 rounded-xl p-12 text-center">
                        <p class="text-gray-500 text-sm">Belum ada jadwal untuk {{ $selectedClassroom->name }}.</p>
                        <p class="text-gray-600 text-xs mt-1">Tambahkan jadwal di form sebelah kiri.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($days as $dayNum => $dayName)
                            @if($schedules->has($dayNum))
                                <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                                    <div class="px-4 py-3 border-b border-white/5 bg-{{ $dayColors[$dayNum] }}-500/5">
                                        <h3 class="text-sm font-semibold text-{{ $dayColors[$dayNum] }}-400">{{ $dayName }}</h3>
                                    </div>
                                    <div class="divide-y divide-white/5">
                                        @foreach($schedules[$dayNum]->sortBy('start_time') as $schedule)
                                            <div class="flex items-center gap-4 px-4 py-3">
                                                <div class="text-xs text-gray-500 flex-shrink-0 w-20 text-center">
                                                    <p>{{ substr($schedule->start_time, 0, 5) }}</p>
                                                    <p class="text-gray-700">↓</p>
                                                    <p>{{ substr($schedule->end_time, 0, 5) }}</p>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold text-white truncate">{{ $schedule->subject->name }}</p>
                                                    <p class="text-xs text-gray-500 mt-0.5">
                                                        {{ $schedule->teacher->name }}
                                                        @if($schedule->room) &middot; {{ $schedule->room }} @endif
                                                    </p>
                                                </div>
                                                <form method="POST" action="{{ route('admin.schedules.destroy', $schedule->id) }}"
                                                      onsubmit="return confirm('Hapus jadwal ini?')">
                                                    @csrf @method('DELETE')
                                                    <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-800 hover:bg-red-900/40 border border-white/10 hover:border-red-500/30 text-gray-400 hover:text-red-400 transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                        </svg>
                                                    </button>
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
        <div class="bg-gray-900 border border-white/5 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
            </svg>
            <p class="text-gray-500 text-sm">Pilih kelas di atas untuk melihat dan mengatur jadwal.</p>
        </div>
    @endif

</x-simans-layout>
