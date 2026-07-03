{{-- resources/views/dashboard/guru.blade.php --}}
<x-simans-layout title="Dashboard Guru">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Kelas"   :value="$stats['total_kelas']"  color="blue"    sub="Semester aktif"/>
        <x-stat-card label="Sesi Aktif"    :value="$stats['sesi_aktif']"   color="emerald" sub="Hari ini"/>
        <x-stat-card label="Siswa Hadir"   :value="$stats['total_hadir']"  color="purple"  sub="Total semua kelas"/>
        <x-stat-card label="Alfa Hari Ini" :value="$stats['total_alfa']"   color="red"     sub="Tanpa keterangan"/>
    </div>

    {{-- Status kelas hari ini --}}
    <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden mb-4">
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
            <h2 class="text-sm font-semibold text-white">Status Absensi Hari Ini</h2>
            <a href="{{ route('guru.attendance.index') }}"
               class="text-xs text-emerald-400 hover:text-emerald-300 font-medium transition-colors">
                Kelola Absensi →
            </a>
        </div>
        @if($classrooms->count() > 0)
            <div class="divide-y divide-white/5">
                @foreach($classrooms as $classroom)
                    @php
                        $session = $todaySessions->firstWhere('classroom_id', $classroom->id);
                    @endphp
                    <div class="flex items-center gap-4 px-5 py-3.5">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-white">{{ $classroom->name }}</p>
                            <p class="text-xs text-gray-500">{{ $classroom->major->code }}</p>
                        </div>
                        @if($session)
                            <span class="text-xs text-gray-400">
                                {{ $session->attendances->count() }} siswa absen
                            </span>
                            @if($session->is_closed)
                                <span class="text-xs bg-gray-800 text-gray-500 px-2.5 py-1 rounded-full border border-white/5">Ditutup</span>
                            @else
                                <span class="flex items-center gap-1.5 text-xs bg-emerald-500/10 text-emerald-400 px-2.5 py-1 rounded-full border border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Aktif
                                </span>
                            @endif
                        @else
                            <form method="POST" action="{{ route('guru.attendance.open') }}">
                                @csrf
                                <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                                <button type="submit"
                                        class="text-xs bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                    Buka Absensi
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-5 py-10 text-center">
                <p class="text-gray-500 text-sm">Belum ada kelas aktif.</p>
            </div>
        @endif
    </div>
</x-simans-layout>
