<x-simans-layout title="Dashboard Kesiswaan">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Dashboard Kesiswaan</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-gray-900 border border-red-500/15 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Alfa Hari Ini</p>
            <p class="text-3xl font-bold text-red-400">{{ $stats['alfa_hari_ini'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Tanpa keterangan</p>
        </div>
        <div class="bg-gray-900 border border-amber-500/15 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Terlambat</p>
            <p class="text-3xl font-bold text-amber-400">{{ $stats['terlambat_hari_ini'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Hari ini</p>
        </div>
        <div class="bg-gray-900 border border-orange-500/15 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Pelanggaran</p>
            <p class="text-3xl font-bold text-orange-400">{{ $stats['pelanggaran_bulan_ini'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Bulan ini</p>
        </div>
        <div class="bg-gray-900 border border-red-500/20 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Siswa Bermasalah</p>
            <p class="text-3xl font-bold text-red-300">{{ $stats['siswa_bermasalah'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Poin ≥ 50</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                <h2 class="text-sm font-semibold text-white">Pelanggaran Terbaru</h2>
                <a href="{{ route('kesiswaan.violations.index') }}" class="text-xs text-emerald-400 hover:text-emerald-300 transition-colors">Lihat semua →</a>
            </div>
            @forelse($recentViolations as $v)
                <div class="flex items-center gap-3 px-5 py-3 border-b border-white/[0.03] last:border-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ $v->student->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $v->category->name ?? '—' }}</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-lg bg-red-500/10 text-red-400 border border-red-500/20 flex-shrink-0">+{{ $v->points }} poin</span>
                    <span class="text-xs text-gray-600 flex-shrink-0">{{ $v->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-gray-500 text-sm">Tidak ada pelanggaran bulan ini.</div>
            @endforelse
        </div>

        <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
            <h2 class="text-sm font-semibold text-white mb-4">Aksi Cepat</h2>
            <div class="space-y-2">
                <a href="{{ route('kesiswaan.violations.index') }}" class="flex items-center gap-3 p-3 bg-gray-800/50 hover:bg-gray-800 border border-white/5 hover:border-white/10 rounded-xl transition-colors group">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-red-400 transition-colors flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <span class="text-sm text-gray-400 group-hover:text-white transition-colors">Catat Pelanggaran</span>
                </a>
                <a href="{{ route('kesiswaan.violations.categories') }}" class="flex items-center gap-3 p-3 bg-gray-800/50 hover:bg-gray-800 border border-white/5 hover:border-white/10 rounded-xl transition-colors group">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-orange-400 transition-colors flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>
                    <span class="text-sm text-gray-400 group-hover:text-white transition-colors">Kategori Pelanggaran</span>
                </a>
            </div>
        </div>
    </div>
</x-simans-layout>
