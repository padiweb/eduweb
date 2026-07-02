<x-simans-layout title="Dashboard Admin">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }} — SMK Alhikmah Tanon</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Siswa" value="10" color="blue" sub="Aktif semester ini"/>
        <x-stat-card label="Total Guru" value="4" color="emerald" sub="Termasuk wali kelas"/>
        <x-stat-card label="Kelas Aktif" value="2" color="purple" sub="Tahun ajaran 2024/2025"/>
        <x-stat-card label="Kehadiran Hari Ini" value="0%" color="amber" sub="Belum ada sesi dibuka"/>
    </div>

    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-white mb-3">Aktivitas Terbaru</h2>
        <p class="text-gray-500 text-sm">Belum ada aktivitas hari ini.</p>
    </div>
</x-simans-layout>