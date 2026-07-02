<x-simans-layout title="Dashboard Siswa">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Halo, {{ auth()->user()->name }}</h1>
        <p class="text-gray-400 text-sm mt-1">NIS: {{ auth()->user()->nis }} — {{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Kehadiran Bulan Ini" value="0%" color="emerald"/>
        <x-stat-card label="Tugas Pending" value="0" color="amber"/>
        <x-stat-card label="Nilai Rata-rata" value="-" color="blue"/>
        <x-stat-card label="Poin Pelanggaran" value="0" color="red"/>
    </div>

    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-white">Status Absensi Hari Ini</h2>
        </div>
        <div class="flex items-center gap-3 p-4 bg-gray-800 rounded-lg">
            <div class="w-10 h-10 rounded-full bg-amber-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-white">Belum Absen</p>
                <p class="text-xs text-gray-400">Scan QR Code dari guru untuk melakukan absensi</p>
            </div>
        </div>
    </div>
</x-simans-layout>