{{-- resources/views/dashboard/siswa.blade.php --}}
<x-simans-layout title="Dashboard Siswa">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Halo, {{ auth()->user()->name }}!</h1>
        <p class="text-gray-400 text-sm mt-1">NIS: {{ auth()->user()->nis }} · {{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- Status absensi hari ini --}}
    <div class="mb-6">
        @if($todayAttendance)
            <div class="bg-gray-900 border {{ $todayAttendance->status === 'hadir' ? 'border-emerald-500/30' : ($todayAttendance->status === 'terlambat' ? 'border-amber-500/30' : 'border-red-500/30') }} rounded-xl p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0
                    {{ $todayAttendance->status === 'hadir' ? 'bg-emerald-500/10' : ($todayAttendance->status === 'terlambat' ? 'bg-amber-500/10' : 'bg-red-500/10') }}">
                    @if($todayAttendance->status === 'hadir')
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($todayAttendance->status === 'terlambat')
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-white">
                        Absensi Hari Ini:
                        <x-status-badge :status="$todayAttendance->status"/>
                    </p>
                    <p class="text-gray-400 text-xs mt-1">
                        @if($todayAttendance->scanned_at)
                            Scan pukul {{ $todayAttendance->scanned_at->format('H:i:s') }} WIB
                        @else
                            Input manual oleh guru
                        @endif
                        @if($todayAttendance->status === 'terlambat')
                            · <span class="text-amber-400">Harap lebih tepat waktu!</span>
                        @endif
                    </p>
                </div>
            </div>
        @else
            <div class="bg-gray-900 border border-amber-500/20 rounded-xl p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-amber-500/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-white">Belum Absen Hari Ini</p>
                    <p class="text-gray-400 text-xs mt-1">
                        Scan QR Code yang ditempel di kelasmu sebelum jam
                        {{ substr(auth()->user()->school->attendance_close_time, 0, 5) }}.
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- Stat bulan ini --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Kehadiran" :value="$rate . '%'"                   color="emerald" sub="Bulan ini"/>
        <x-stat-card label="Hadir"     :value="$monthStats['hadir'] ?? 0"     color="blue"    sub="Tepat waktu"/>
        <x-stat-card label="Terlambat" :value="$monthStats['terlambat'] ?? 0" color="amber"   sub="Bulan ini"/>
        <x-stat-card label="Alfa"      :value="$monthStats['alfa'] ?? 0"      color="red"     sub="Tanpa keterangan"/>
    </div>

    {{-- Poin pelanggaran --}}
    @if($violationPoints > 0)
        <div class="bg-red-900/20 border border-red-500/20 rounded-xl p-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-semibold text-red-300">Total Poin Pelanggaran: {{ $violationPoints }}</p>
                <p class="text-xs text-red-400/70 mt-0.5">Lihat detail di menu Pelanggaran</p>
            </div>
        </div>
    @endif

    {{-- Link riwayat --}}
    <div class="mt-4">
        <a href="{{ route('siswa.attendance.history') }}"
           class="inline-flex items-center gap-2 text-sm text-emerald-400 hover:text-emerald-300 font-medium transition-colors">
            Lihat riwayat absensi lengkap →
        </a>
    </div>
</x-simans-layout>


{{-- ============================================================ --}}
{{-- resources/views/dashboard/kesiswaan.blade.php               --}}
{{-- ============================================================ --}}
