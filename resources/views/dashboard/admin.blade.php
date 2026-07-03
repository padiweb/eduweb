{{-- resources/views/dashboard/admin.blade.php --}}
<x-simans-layout title="Dashboard Admin">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }} · {{ auth()->user()->school->name }}</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Siswa"     :value="$stats['siswa']"          color="blue"    sub="Aktif semester ini"/>
        <x-stat-card label="Total Guru"      :value="$stats['guru']"           color="emerald" sub="Termasuk wali kelas"/>
        <x-stat-card label="Kelas Aktif"     :value="$stats['kelas']"          color="purple"  sub="Tahun ajaran aktif"/>
        <x-stat-card label="Sesi Hari Ini"   :value="$stats['sesi_hari_ini']"  color="amber"   sub="Absensi dibuka"/>
    </div>

    <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5">
            <h2 class="text-sm font-semibold text-white">Absensi Hari Ini</h2>
        </div>
        @if($recentSessions->count() > 0)
            <div class="divide-y divide-white/5">
                @foreach($recentSessions as $session)
                    <div class="flex items-center gap-4 px-5 py-3.5">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-white">{{ $session->classroom->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Dibuka oleh {{ $session->openedBy?->name ?? 'Sistem (Otomatis)' }} · {{ $session->created_at->format('H:i') }}
                            </p>
                        </div>
                        <span class="text-sm text-gray-400">
                            {{ $session->attendances->count() }} siswa
                        </span>
                        @if($session->is_closed)
                            <span class="text-xs bg-gray-800 text-gray-500 px-2.5 py-1 rounded-full border border-white/5">Ditutup</span>
                        @else
                            <span class="text-xs bg-emerald-500/10 text-emerald-400 px-2.5 py-1 rounded-full border border-emerald-500/20">Aktif</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-5 py-10 text-center">
                <p class="text-gray-500 text-sm">Belum ada sesi absensi hari ini.</p>
            </div>
        @endif
    </div>
</x-simans-layout>
