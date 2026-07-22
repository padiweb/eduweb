<x-simans-layout title="Dashboard Bendahara">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Dashboard Keuangan</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }} · {{ auth()->user()->school->name }}</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-gray-900 border border-emerald-500/20 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Pemasukan Bulan Ini</p>
            <p class="text-xl font-bold text-emerald-400">Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-600 mt-1">Sudah diverifikasi</p>
        </div>
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Tagihan Bulan Ini</p>
            <p class="text-3xl font-bold text-white">{{ $stats['tagihan_bulan_ini'] }}</p>
        </div>
        <div class="bg-gray-900 border border-amber-500/15 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Menunggu Konfirmasi</p>
            <p class="text-3xl font-bold text-amber-400">{{ $stats['menunggu_konfirmasi'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Transfer masuk</p>
        </div>
        <div class="bg-gray-900 border border-red-500/15 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Total Tunggakan</p>
            <p class="text-3xl font-bold text-red-400">{{ $stats['total_tunggakan'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Belum lunas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Transfer menunggu konfirmasi --}}
        <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                <h2 class="text-sm font-semibold text-white">Transfer Perlu Dikonfirmasi</h2>
                <a href="{{ route('bendahara.bills.index') }}" class="text-xs text-emerald-400 hover:text-emerald-300 transition-colors">Lihat semua →</a>
            </div>
            @forelse($pendingTransfers as $tx)
                <div class="flex items-center gap-3 px-5 py-3 border-b border-white/[0.03] last:border-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ $tx->bill?->student?->name }}</p>
                        <p class="text-xs text-gray-500">{{ $tx->bill?->paymentType?->name }}</p>
                    </div>
                    <span class="text-sm font-semibold text-amber-400 flex-shrink-0">Rp {{ number_format($tx->amount, 0, ',', '.') }}</span>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-gray-500 text-sm">Tidak ada transfer menunggu.</div>
            @endforelse
        </div>

        {{-- Tunggakan per kelas --}}
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
            <h2 class="text-sm font-semibold text-white mb-4">Tunggakan Per Kelas</h2>
            @forelse($tunggakanPerKelas as $kelas => $count)
                <div class="flex items-center gap-3 mb-3 last:mb-0">
                    <p class="flex-1 text-sm text-gray-300 truncate">{{ $kelas }}</p>
                    <div class="flex-1 bg-gray-800 rounded-full h-1.5 max-w-24">
                        <div class="h-1.5 rounded-full bg-red-500" style="width:{{ min(100, $count * 5) }}%"></div>
                    </div>
                    <span class="text-xs text-red-400 font-medium w-8 text-right flex-shrink-0">{{ $count }}</span>
                </div>
            @empty
                <p class="text-gray-500 text-sm text-center py-4">Tidak ada tunggakan.</p>
            @endforelse
        </div>
    </div>
</x-simans-layout>
