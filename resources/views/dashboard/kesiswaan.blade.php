{{-- resources/views/dashboard/kesiswaan.blade.php --}}
<x-simans-layout title="Dashboard Kesiswaan">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card
            label="Pelanggaran Bulan Ini"
            :value="$stats['pelanggaran_bulan_ini']"
            color="red"
            sub="Semua kategori"/>
        <x-stat-card
            label="Alfa Hari Ini"
            :value="$stats['alfa_hari_ini']"
            color="amber"
            sub="Tanpa keterangan"/>
        <x-stat-card
            label="Terlambat Hari Ini"
            :value="$stats['terlambat_hari_ini']"
            color="purple"
            sub="Seluruh kelas"/>
    </div>

    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-white mb-3">Menu Kesiswaan</h2>
        <div class="grid grid-cols-2 gap-3">
            <a href="#" class="flex items-center gap-3 bg-gray-800 hover:bg-gray-700 border border-white/5 rounded-xl p-4 transition-colors">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
                <span class="text-sm font-medium text-white">Input Pelanggaran</span>
            </a>
            <a href="#" class="flex items-center gap-3 bg-gray-800 hover:bg-gray-700 border border-white/5 rounded-xl p-4 transition-colors">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                </svg>
                <span class="text-sm font-medium text-white">Rekap Absensi</span>
            </a>
        </div>
    </div>
</x-simans-layout>
