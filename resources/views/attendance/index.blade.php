<x-simans-layout title="Absensi Siswa">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Absensi Siswa</h1>
            <p class="text-gray-400 text-sm mt-1">
                Klik kelas untuk membuka sesi atau lihat rekap absensi hari ini
            </p>
        </div>
        <div class="text-right">
            <p class="text-white font-semibold">{{ now()->translatedFormat('l, d F Y') }}</p>
            <p class="text-gray-400 text-xs mt-0.5">
                Jam masuk: {{ substr(auth()->user()->school->school_start_time, 0, 5) }} —
                Jam tutup: {{ substr(auth()->user()->school->attendance_close_time, 0, 5) }}
            </p>
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

    {{-- Grid kelas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($classrooms as $classroom)
            @php
                $session = $todaySessions->get($classroom->id);
                $hasSession = $session !== null;
                $attended   = $hasSession ? $session->attendances->count() : 0;
                $total      = $classroom->students->count() ?? 0;
                $rate       = $total > 0 ? round(($attended / $total) * 100) : 0;
            @endphp

            <div class="bg-gray-900 border {{ $hasSession ? 'border-emerald-500/30' : 'border-white/5' }} rounded-xl overflow-hidden">

                {{-- Header kelas --}}
                <div class="flex items-start justify-between p-5 pb-4">
                    <div>
                        <h3 class="font-bold text-white text-lg">{{ $classroom->name }}</h3>
                        <p class="text-gray-400 text-sm">{{ $classroom->major->name }}</p>
                        <p class="text-gray-600 text-xs mt-1">{{ $total }} siswa</p>
                    </div>

                    @if($hasSession)
                        <span class="flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $session->is_closed
                                ? 'bg-gray-800 text-gray-400 border border-white/10'
                                : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $session->is_closed ? 'bg-gray-500' : 'bg-emerald-400 animate-pulse' }}"></span>
                            {{ $session->is_closed ? 'Ditutup' : 'Aktif' }}
                        </span>
                    @else
                        <span class="text-xs text-gray-600 bg-gray-800 border border-white/5 px-2.5 py-1 rounded-full">
                            Belum dibuka
                        </span>
                    @endif
                </div>

                {{-- Progress bar kehadiran --}}
                @if($hasSession && $total > 0)
                    <div class="px-5 mb-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                            <span>{{ $attended }} / {{ $total }} hadir</span>
                            <span class="{{ $rate >= 80 ? 'text-emerald-400' : ($rate >= 60 ? 'text-amber-400' : 'text-red-400') }}">{{ $rate }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all"
                                 style="width: {{ $rate }}%"></div>
                        </div>
                        {{-- Status breakdown --}}
                        <div class="flex gap-3 mt-2">
                            @if($session->attendances->where('status', 'hadir')->count() > 0)
                                <span class="text-xs text-emerald-400">{{ $session->attendances->where('status', 'hadir')->count() }} hadir</span>
                            @endif
                            @if($session->attendances->where('status', 'terlambat')->count() > 0)
                                <span class="text-xs text-amber-400">{{ $session->attendances->where('status', 'terlambat')->count() }} terlambat</span>
                            @endif
                            @if($session->attendances->where('status', 'alfa')->count() > 0)
                                <span class="text-xs text-red-400">{{ $session->attendances->where('status', 'alfa')->count() }} alfa</span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Aksi --}}
                <div class="px-5 pb-5 flex gap-2">
                    @if($hasSession)
                        <a href="{{ route('guru.attendance.show', $session->id) }}"
                           class="flex-1 text-center text-sm font-semibold py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-white border border-white/10 transition-colors">
                            Lihat Detail
                        </a>
                        @if(! $session->is_closed)
                            <form method="POST" action="{{ route('guru.attendance.open') }}">
                                @csrf
                                <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                                <button type="submit"
                                        class="px-4 py-2 text-sm font-medium rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-400 border border-white/10 transition-colors"
                                        title="Perbarui QR">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    @else
                        <form method="POST" action="{{ route('guru.attendance.open') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                            <button type="submit"
                                    class="w-full text-sm font-semibold py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white transition-colors">
                                + Buka Absensi
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-gray-900 border border-white/5 rounded-xl p-12 text-center">
                <p class="text-gray-500">Belum ada kelas aktif di tahun ajaran ini.</p>
                <p class="text-gray-600 text-sm mt-1">Hubungi admin untuk menambahkan kelas.</p>
            </div>
        @endforelse
    </div>

</x-simans-layout>