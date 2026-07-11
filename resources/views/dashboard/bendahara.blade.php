<x-simans-layout title="Dashboard Bendahara">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }} · {{ auth()->user()->school->name }}</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Tagihan Bulan Ini"  :value="$stats['tagihan_bulan_ini']"   color="purple" sub="Total dibuat"/>
        <x-stat-card label="Sudah Lunas"         :value="$stats['lunas_bulan_ini']"     color="green"  sub="Bulan ini"/>
        <x-stat-card label="Tunggu Konfirmasi"   :value="$stats['menunggu_konfirmasi']" color="amber"  sub="Bukti transfer"/>
        <x-stat-card label="Tunggakan"           :value="$stats['total_tunggakan']"     color="red"    sub="Belum/cicilan"/>
    </div>

    {{-- Pending transfer --}}
    @if(isset($pendingTransfers) && $pendingTransfers->isNotEmpty())
    <div class="bg-gray-900 border border-amber-500/30 rounded-xl p-5 mb-6">
        <h2 class="text-sm font-semibold text-amber-400 mb-3">Bukti Transfer Menunggu Konfirmasi</h2>
        <div class="space-y-2">
            @foreach($pendingTransfers as $trx)
            <div class="flex items-center justify-between bg-gray-800 rounded-lg px-4 py-3">
                <div>
                    <p class="text-sm font-medium text-white">{{ $trx->bill->student->name ?? '-' }}</p>
                    <p class="text-xs text-gray-400">{{ $trx->bill->paymentType->name ?? '-' }} · Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                </div>
                <a href="#" class="text-xs bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 border border-amber-500/30 rounded-lg px-3 py-1.5 transition-colors">
                    Konfirmasi
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Menu --}}
    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-white mb-3">Menu Bendahara</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <a href="{{ route('bendahara.payment-types.index') }}" class="flex items-center gap-3 bg-gray-800 hover:bg-gray-700 border border-white/5 rounded-xl p-4 transition-colors">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-sm font-medium text-white">Jenis & Tarif</span>
            </a>
            <a href="{{ route('bendahara.bills.index') }}" class="flex items-center gap-3 bg-gray-800 hover:bg-gray-700 border border-white/5 rounded-xl p-4 transition-colors">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-sm font-medium text-white">Kelola Tagihan</span>
            </a>
            <a href="{{ route('bendahara.transactions.index') }}" class="flex items-center gap-3 bg-gray-800 hover:bg-gray-700 border border-white/5 rounded-xl p-4 transition-colors">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                <span class="text-sm font-medium text-white">Konfirmasi Transfer</span>
            </a>
            <a href="{{ route('bendahara.bills.index', ['status' => 'unpaid']) }}" class="flex items-center gap-3 bg-gray-800 hover:bg-gray-700 border border-white/5 rounded-xl p-4 transition-colors">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                <span class="text-sm font-medium text-white">Daftar Tunggakan</span>
            </a>
            <a href="{{ route('bendahara.bills.create') }}" class="flex items-center gap-3 bg-gray-800 hover:bg-gray-700 border border-white/5 rounded-xl p-4 transition-colors">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span class="text-sm font-medium text-white">Buat Tagihan</span>
            </a>
        </div>
    </div>
</x-simans-layout>