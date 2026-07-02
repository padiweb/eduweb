<x-simans-layout title="Dashboard Kesiswaan">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <x-stat-card label="Pelanggaran Bulan Ini" value="0" color="red"/>
        <x-stat-card label="Siswa Alfa Hari Ini" value="0" color="amber"/>
        <x-stat-card label="Izin Pending" value="0" color="blue"/>
    </div>

    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-white mb-3">Rekap Absensi Hari Ini</h2>
        <p class="text-gray-500 text-sm">Belum ada data absensi hari ini.</p>
    </div>
</x-simans-layout>