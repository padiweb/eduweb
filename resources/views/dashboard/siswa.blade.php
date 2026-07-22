<x-simans-layout title="Dashboard">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-white">Halo, {{ auth()->user()->name }}!</h1>
        <p class="text-gray-400 text-xs mt-0.5">NIS: {{ auth()->user()->nis }} · {{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- Status absensi hari ini --}}
    @php
        $statusColor = match($todayAttendance?->status) {
            'hadir'     => 'emerald',
            'terlambat' => 'amber',
            'alfa'      => 'red',
            default     => 'gray'
        };
        $statusText = match($todayAttendance?->status) {
            'hadir'     => 'Hadir Tepat Waktu',
            'terlambat' => 'Hadir Terlambat',
            'alfa'      => 'Tidak Hadir',
            default     => 'Belum Absen Hari Ini'
        };
    @endphp
    <div class="bg-gray-900 border border-{{ $statusColor }}-500/20 rounded-2xl p-4 mb-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-{{ $statusColor }}-500/10 flex items-center justify-center flex-shrink-0">
            @if($todayAttendance?->status === 'hadir')
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @elseif($todayAttendance?->status === 'terlambat')
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @else
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            @endif
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-white">{{ $statusText }}</p>
            <p class="text-xs text-gray-500 mt-0.5">
                @if($todayAttendance?->scanned_at)
                    Scan pukul {{ $todayAttendance->scanned_at->format('H:i') }} WIB
                @else
                    Scan QR sebelum {{ substr(auth()->user()->school->attendance_close_time ?? '07:30:00', 0, 5) }}
                @endif
            </p>
        </div>
    </div>

    {{-- Stats bulan ini --}}
    <div class="grid grid-cols-4 gap-2 mb-4">
        <div class="bg-gray-900 border border-white/5 rounded-xl p-3 text-center">
            <p class="text-xl font-bold text-emerald-400">{{ $rate }}%</p>
            <p class="text-xs text-gray-500 mt-0.5">Kehadiran</p>
        </div>
        <div class="bg-gray-900 border border-white/5 rounded-xl p-3 text-center">
            <p class="text-xl font-bold text-blue-400">{{ $monthStats['hadir'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Hadir</p>
        </div>
        <div class="bg-gray-900 border border-white/5 rounded-xl p-3 text-center">
            <p class="text-xl font-bold text-amber-400">{{ $monthStats['terlambat'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Terlambat</p>
        </div>
        <div class="bg-gray-900 border border-white/5 rounded-xl p-3 text-center">
            <p class="text-xl font-bold text-red-400">{{ $monthStats['alfa'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Alfa</p>
        </div>
    </div>

    {{-- Alert poin pelanggaran --}}
    @if($violationPoints > 0)
        <div class="bg-red-500/5 border border-red-500/20 rounded-xl p-3 mb-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <div class="flex-1">
                <p class="text-sm font-semibold text-red-300">Poin Pelanggaran: {{ $violationPoints }}</p>
                <a href="{{ route('siswa.violations') }}" class="text-xs text-red-400/70 hover:text-red-400 transition-colors">Lihat detail →</a>
            </div>
        </div>
    @endif

    {{-- Alert tagihan --}}
    @if($activeBills > 0)
        <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl p-3 mb-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
            <div class="flex-1">
                <p class="text-sm font-semibold text-amber-300">{{ $activeBills }} Tagihan Belum Lunas</p>
                <a href="{{ route('siswa.payment.index') }}" class="text-xs text-amber-400/70 hover:text-amber-400 transition-colors">Bayar sekarang →</a>
            </div>
        </div>
    @endif

    {{-- Prakerin aktif --}}
    @if($prakerinActive)
        <div class="bg-blue-500/5 border border-blue-500/20 rounded-xl p-3 mb-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
            <div class="flex-1">
                <p class="text-sm font-semibold text-blue-300">Prakerin Aktif</p>
                <p class="text-xs text-blue-400/70">{{ $prakerinActive->location->name }}</p>
            </div>
            <a href="{{ route('siswa.prakerin.index') }}" class="text-xs text-blue-400 hover:text-blue-300 transition-colors">Buka →</a>
        </div>
    @endif

    {{-- Link riwayat --}}
    <a href="{{ route('siswa.attendance.history') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors">
        Riwayat absensi lengkap
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
    </a>
</x-simans-layout>
