<x-simans-layout title="Dashboard Kepala Sekolah">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Dashboard</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }} · {{ auth()->user()->school->name }}</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Total Siswa Aktif</p>
            <p class="text-3xl font-bold text-white">{{ $stats['total_siswa'] }}</p>
        </div>
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Total Guru</p>
            <p class="text-3xl font-bold text-white">{{ $stats['total_guru'] }}</p>
        </div>
        <div class="bg-gray-900 border border-emerald-500/20 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Hadir Hari Ini</p>
            <p class="text-3xl font-bold text-emerald-400">{{ $stats['hadir_hari_ini'] }}</p>
        </div>
        <div class="bg-gray-900 border border-amber-500/15 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Pemasukan Bulan Ini</p>
            <p class="text-2xl font-bold text-amber-400">Rp {{ number_format($pemasukanBulan, 0, ',', '.') }}</p>
        </div>
        <div class="bg-gray-900 border border-red-500/15 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Tunggakan</p>
            <p class="text-3xl font-bold text-red-400">{{ $stats['tunggakan'] }}</p>
            <p class="text-xs text-gray-600 mt-1">tagihan belum lunas</p>
        </div>
        <div class="bg-gray-900 border border-orange-500/15 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Pelanggaran</p>
            <p class="text-3xl font-bold text-orange-400">{{ $stats['pelanggaran_bulan'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Bulan ini</p>
        </div>
    </div>

    {{-- Rekap kelas hari ini --}}
    <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5">
            <h2 class="text-sm font-semibold text-white">Rekap Kelas Hari Ini</h2>
        </div>
        <div class="divide-y divide-white/5">
            @forelse($classroomSummary as $classroom)
                @php
                    $session = $classroom->attendanceSessions->first();
                    $hadir   = $session?->attendances->whereIn('status', ['hadir','terlambat'])->count() ?? 0;
                    $total   = $classroom->students->count();
                    $pct     = $total > 0 ? round($hadir / $total * 100) : 0;
                @endphp
                <div class="flex items-center gap-4 px-5 py-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white">{{ $classroom->name }}</p>
                        <p class="text-xs text-gray-500">{{ $classroom->major->code ?? '' }} · {{ $total }} siswa</p>
                    </div>
                    <div class="w-24 hidden sm:block">
                        <div class="w-full bg-gray-800 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full {{ $pct >= 80 ? 'bg-emerald-500' : ($pct >= 60 ? 'bg-amber-500' : 'bg-red-500') }}"
                                 style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $hadir }}/{{ $total }}</span>
                    @if(!$session)
                        <span class="text-xs px-2 py-0.5 rounded-lg bg-gray-800 text-gray-600 border border-white/5 flex-shrink-0">Belum</span>
                    @elseif($session->is_closed)
                        <span class="text-xs px-2 py-0.5 rounded-lg bg-gray-800 text-gray-500 border border-white/5 flex-shrink-0">Selesai</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex-shrink-0">Aktif</span>
                    @endif
                </div>
            @empty
                <div class="px-5 py-8 text-center text-gray-500 text-sm">Belum ada kelas aktif.</div>
            @endforelse
        </div>
    </div>
</x-simans-layout>
