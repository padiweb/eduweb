<x-simans-layout title="Dashboard Guru">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Sesi Hari Ini" value="0" color="blue" sub="Belum ada sesi dibuka"/>
        <x-stat-card label="Siswa Hadir" value="0" color="emerald" sub="Dari total siswa"/>
        <x-stat-card label="Belum Absen" value="0" color="amber" sub="Perlu konfirmasi"/>
        <x-stat-card label="Alfa" value="0" color="red" sub="Tanpa keterangan"/>
    </div>

    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-white mb-3">Jadwal Hari Ini</h2>
        <p class="text-gray-500 text-sm">Belum ada jadwal untuk hari ini.</p>
    </div>
</x-simans-layout>