<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — SiManS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-950 text-white antialiased">

<div class="flex h-full min-h-screen">

    {{-- ===== SIDEBAR ===== --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 border-r border-white/5 flex flex-col transition-transform duration-300 lg:translate-x-0 -translate-x-full">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/5">
            <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-white text-sm leading-tight">SiManS</p>
                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->school->name }}</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
            @php $role = auth()->user()->role; @endphp

            <x-sidebar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="home">
                Beranda
            </x-sidebar-link>

            {{-- ── SISWA ── --}}
            @if($role === 'siswa')
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Akademik</p>
                </div>
                <x-sidebar-link href="{{ route('siswa.siswa.dashboard') }}" :active="request()->routeIs('siswa.siswa.dashboard')" icon="chart">
                    Dashboard
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('siswa.attendance.absensi') }}" :active="request()->routeIs('siswa.attendance.*')" icon="qrcode">
                    Absensi
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('siswa.assignments.index') }}" :active="request()->routeIs('siswa.assignments.*')" icon="book">
                    Tugas & Nilai
                </x-sidebar-link>
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Keuangan</p>
                </div>
                <x-sidebar-link href="{{ route('siswa.payment.index') }}" :active="request()->routeIs('siswa.payment.*')" icon="credit-card">
                    Status Pembayaran
                </x-sidebar-link>
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Informasi</p>
                </div>
                <x-sidebar-link href="{{ route('siswa.violations') }}" :active="request()->routeIs('siswa.violations')" icon="shield">
                    Pelanggaran
                </x-sidebar-link>
            @endif

            {{-- ── GURU / WALI KELAS ── --}}
            @if(in_array($role, ['guru', 'wali_kelas']))
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Kelas</p>
                </div>
                <x-sidebar-link href="{{ route('guru.dashboard') }}" :active="request()->routeIs('guru.dashboard')" icon="chart">
                    Dashboard
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('guru.attendance.index') }}" :active="request()->routeIs('guru.attendance.*')" icon="clipboard">
                    Absensi Siswa
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('guru.assignments.index') }}" :active="request()->routeIs('guru.assignments.*')" icon="book">
                    Tugas & Nilai
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('guru.journal.index') }}" :active="request()->routeIs('guru.journal.*')" icon="journal">
                    Jurnal Mengajar
                </x-sidebar-link>
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Kehadiran</p>
                </div>
                <x-sidebar-link href="{{ route('guru.teacher-attendance.index') }}" :active="request()->routeIs('guru.teacher-attendance.*')" icon="clock">
                    Absensi Saya
                </x-sidebar-link>
            @endif

            {{-- ── KESISWAAN ── --}}
            @if($role === 'kesiswaan')
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Kesiswaan</p>
                </div>
                <x-sidebar-link href="{{ route('kesiswaan.dashboard') }}" :active="request()->routeIs('kesiswaan.dashboard')" icon="chart">
                    Dashboard
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('kesiswaan.violations.index') }}" :active="request()->routeIs('kesiswaan.violations.*')" icon="shield">
                    Pelanggaran
                </x-sidebar-link>
            @endif

            {{-- ── BENDAHARA ── --}}
            @if($role === 'bendahara')
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Pembayaran</p>
                </div>
                <x-sidebar-link href="{{ route('bendahara.dashboard') }}" :active="request()->routeIs('bendahara.dashboard')" icon="chart">
                    Dashboard
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('bendahara.bills.index') }}" :active="request()->routeIs('bendahara.bills.*')" icon="credit-card">
                    Kelola Tagihan
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('bendahara.transactions.index') }}" :active="request()->routeIs('bendahara.transactions.*')" icon="clipboard">
                    Konfirmasi Transfer
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('bendahara.payment-types.index') }}" :active="request()->routeIs('bendahara.payment-types.*')" icon="cog">
                    Jenis & Tarif
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('bendahara.discounts.index') }}" :active="request()->routeIs('bendahara.discounts.*')" icon="shield">
                    Beasiswa
                </x-sidebar-link>
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Laporan</p>
                </div>
                <x-sidebar-link href="{{ route('bendahara.bills.index', ['status' => 'unpaid']) }}" :active="false" icon="shield">
                    Daftar Tunggakan
                </x-sidebar-link>
            @endif

            {{-- ── KEPALA SEKOLAH ── --}}
            @if($role === 'kepala_sekolah')
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Monitoring</p>
                </div>
                <x-sidebar-link href="{{ route('kepala.dashboard') }}" :active="request()->routeIs('kepala.dashboard')" icon="chart">
                    Dashboard
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('bendahara.bills.index') }}" :active="request()->routeIs('bendahara.bills.*')" icon="credit-card">
                    Data Tagihan
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('bendahara.transactions.index') }}" :active="request()->routeIs('bendahara.transactions.*')" icon="clipboard">
                    Riwayat Transfer
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('bendahara.bills.index', ['status' => 'unpaid']) }}" :active="false" icon="shield">
                    Tunggakan
                </x-sidebar-link>
            @endif

            {{-- ── ADMIN ── --}}
            @if($role === 'admin')
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Manajemen</p>
                </div>
                <x-sidebar-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" icon="chart">
                    Dashboard
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')" icon="users">
                    Manajemen User
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.classrooms.index') }}" :active="request()->routeIs('admin.classrooms.*')" icon="school">
                    Manajemen Kelas
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.subjects.index') }}" :active="request()->routeIs('admin.subjects.*')" icon="book">
                    Mata Pelajaran
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.schedules.index') }}" :active="request()->routeIs('admin.schedules.*')" icon="calendar">
                    Jadwal Mengajar
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.promotions.index') }}" :active="request()->routeIs('admin.promotions.*')" icon="arrow-up">
                    Promosi Siswa
                </x-sidebar-link>
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Monitoring</p>
                </div>
                <x-sidebar-link href="{{ route('admin.teacher-attendance.index') }}" icon="users">
                    Absensi Guru
                </x-sidebar-link>
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600">Sistem</p>
                </div>
                <x-sidebar-link href="{{ route('admin.settings.school') }}" :active="request()->routeIs('admin.settings.*')" icon="cog">
                    Pengaturan Sekolah
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.qr.index') }}" :active="request()->routeIs('admin.qr.*')" icon="qrcode">
                    Kelola QR Kelas
                </x-sidebar-link>
            @endif
        </nav>

        {{-- User card --}}
        <div class="p-3 border-t border-white/5">
            <div class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-white/5 transition-colors">
                <div class="w-8 h-8 rounded-full bg-emerald-900 border border-emerald-700/50 flex items-center justify-center text-xs font-bold text-emerald-400 flex-shrink-0">
                    {{ auth()->user()->initials }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-red-400 transition-colors" title="Keluar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ===== MAIN AREA ===== --}}
    <div class="flex-1 flex flex-col min-h-screen lg:pl-64">

        {{-- Topbar --}}
        <header class="sticky top-0 z-40 h-14 bg-gray-900/95 backdrop-blur border-b border-white/5 flex items-center gap-4 px-4 lg:px-6">
            <button id="sidebar-toggle" class="lg:hidden text-gray-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>
            <span class="text-sm font-semibold text-white">{{ $title ?? 'Dashboard' }}</span>
            <div class="flex-1"></div>
            <span class="hidden sm:block text-xs text-gray-500 bg-gray-800 px-3 py-1.5 rounded-full border border-white/5">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-4 lg:p-6">
            @if(session('success'))
                <div class="mb-5 flex items-center gap-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-300 px-4 py-3 rounded-xl text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-5 flex items-center gap-3 bg-red-900/30 border border-red-700/40 text-red-300 px-4 py-3 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-5 bg-red-900/30 border border-red-700/40 text-red-300 px-4 py-3 rounded-xl text-sm">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="flex items-center gap-2">
                                <span class="w-1 h-1 rounded-full bg-red-400 flex-shrink-0"></span>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>

<div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black/60 hidden lg:hidden"></div>

<script>
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebar-overlay');
    const toggleBtn = document.getElementById('sidebar-toggle');
    function openSidebar()  { sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); }
    function closeSidebar() { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); }
    toggleBtn?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);
</script>

@stack('scripts')
</body>
</html>
