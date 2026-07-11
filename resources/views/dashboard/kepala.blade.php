<x-simans-layout title="Dashboard Kepala Sekolah">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }} · {{ auth()->user()->school->name }}</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Siswa"        :value="$stats['total_siswa']"       color="blue"   sub="Siswa aktif"/>
        <x-stat-card label="Tagihan Bulan Ini"  :value="$stats['tagihan_bulan_ini']" color="purple" sub="Total dibuat"/>
        <x-stat-card label="Sudah Bayar"        :value="$stats['sudah_bayar']"       color="green"  sub="Bulan ini"/>
        <x-stat-card label="Tunggakan"          :value="$stats['tunggakan']"         color="red"    sub="Belum/cicilan"/>
    </div>

    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-white mb-3">Menu Kepala Sekolah</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <a href="{{ route('bendahara.bills.index') }}" class="flex items-center gap-3 bg-gray-800 hover:bg-gray-700 border border-white/5 rounded-xl p-4 transition-colors">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-sm font-medium text-white">Lihat Tagihan</span>
            </a>
            <a href="{{ route('bendahara.bills.index', ['status' => 'unpaid']) }}" class="flex items-center gap-3 bg-gray-800 hover:bg-gray-700 border border-white/5 rounded-xl p-4 transition-colors">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                <span class="text-sm font-medium text-white">Daftar Tunggakan</span>
            </a>
        </div>
    </div>
</x-simans-layout>