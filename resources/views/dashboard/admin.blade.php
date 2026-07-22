<x-simans-layout title="Dashboard">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Dashboard Admin</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }} · {{ auth()->user()->school->name }}</p>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Total Siswa Aktif</p>
            <p class="text-3xl font-bold text-white">{{ $stats['siswa'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Terdaftar di sekolah</p>
        </div>
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Total Guru</p>
            <p class="text-3xl font-bold text-white">{{ $stats['guru'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Termasuk wali kelas</p>
        </div>
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Kelas Aktif</p>
            <p class="text-3xl font-bold text-white">{{ $stats['kelas'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Tahun ajaran aktif</p>
        </div>
        <div class="bg-gray-900 border border-emerald-500/20 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Hadir Hari Ini</p>
            <p class="text-3xl font-bold text-emerald-400">{{ $stats['hadir_hari_ini'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Hadir + terlambat</p>
        </div>
        <div class="bg-gray-900 border border-red-500/15 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Alfa Hari Ini</p>
            <p class="text-3xl font-bold text-red-400">{{ $stats['alfa_hari_ini'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Tanpa keterangan</p>
        </div>
        <div class="bg-gray-900 border border-amber-500/15 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Tunggakan SPP</p>
            <p class="text-3xl font-bold text-amber-400">{{ $stats['tunggakan'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Belum lunas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        {{-- Absensi hari ini --}}
        <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                <h2 class="text-sm font-semibold text-white">Absensi Hari Ini</h2>
                <a href="{{ route('admin.teacher-attendance.index') }}" class="text-xs text-emerald-400 hover:text-emerald-300 transition-colors">Lihat semua →</a>
            </div>
            @forelse($recentSessions as $session)
                <div class="flex items-center gap-3 px-5 py-3 border-b border-white/[0.03] last:border-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ $session->classroom->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $session->openedBy?->name ?? 'Otomatis' }} · {{ $session->created_at->format('H:i') }}</p>
                    </div>
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $session->attendances->count() }} siswa</span>
                    @if($session->is_closed)
                        <span class="text-xs px-2 py-0.5 rounded-lg bg-gray-800 text-gray-500 border border-white/5 flex-shrink-0">Tutup</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex-shrink-0 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Aktif
                        </span>
                    @endif
                </div>
            @empty
                <div class="px-5 py-8 text-center text-gray-500 text-sm">Belum ada sesi absensi hari ini.</div>
            @endforelse
        </div>

        {{-- Quick access --}}
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
            <h2 class="text-sm font-semibold text-white mb-4">Akses Cepat</h2>
            <div class="grid grid-cols-2 gap-2">
                @php
                    $menus = [
                        ['route' => 'admin.users.index', 'label' => 'Manajemen User', 'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
                        ['route' => 'admin.classrooms.index', 'label' => 'Kelas', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                        ['route' => 'admin.schedules.index', 'label' => 'Jadwal', 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
                        ['route' => 'admin.subjects.index', 'label' => 'Mata Pelajaran', 'icon' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25'],
                        ['route' => 'admin.prakerin.periods.index', 'label' => 'Prakerin', 'icon' => 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0'],
                        ['route' => 'admin.settings.school', 'label' => 'Pengaturan', 'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                    ];
                @endphp
                @foreach($menus as $menu)
                    <a href="{{ route($menu['route']) }}"
                       class="flex items-center gap-3 p-3 bg-gray-800/50 hover:bg-gray-800 border border-white/5 hover:border-white/10 rounded-xl transition-colors group">
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-emerald-400 transition-colors flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $menu['icon'] }}"/>
                        </svg>
                        <span class="text-sm text-gray-400 group-hover:text-white transition-colors">{{ $menu['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-simans-layout>
