<x-simans-layout title="Absensi Siswa">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Absensi Siswa</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola sesi absensi dan rekap kehadiran</p>
        </div>
        <a href="{{ route('guru.attendance.create') }}"
           class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buka Sesi Absensi
        </a>
    </div>

    {{-- Sesi aktif hari ini --}}
    @if($activeSessions->count() > 0)
        <div class="mb-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Sesi Hari Ini</h2>
            <div class="space-y-3">
                @foreach($activeSessions as $session)
                    <div class="bg-white border border-gray-200 rounded-xl p-5 flex items-center gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-gray-900 font-semibold">{{ $session->subject->name }}</span>
                                <span class="text-gray-500">—</span>
                                <span class="text-gray-500 text-sm">{{ $session->classroom->name }}</span>
                                @if($session->isActive())
                                    <span class="flex items-center gap-1.5 text-xs font-semibold text-blue-600 bg-blue-600/10 border border-blue-200 px-2.5 py-0.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="text-xs font-semibold text-gray-500 bg-white border border-gray-200 px-2.5 py-0.5 rounded-full">
                                        Ditutup
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                <span>Dibuka {{ $session->created_at->format('H:i') }}</span>
                                <span>{{ $session->attendances->count() }} / {{ $session->classroom->students->count() }} siswa</span>
                                @if($session->isActive())
                                    <span class="text-amber-400">Kedaluwarsa {{ $session->token_expires_at->format('H:i') }}</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('guru.attendance.show', $session->id) }}"
                           class="text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors">
                            Lihat Detail →
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center mb-6">
            <svg class="w-12 h-12 text-blue-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
            </svg>
            <p class="text-gray-500 text-sm">Belum ada sesi absensi hari ini</p>
            <a href="{{ route('guru.attendance.create') }}" class="inline-block mt-3 text-blue-600 hover:text-blue-700 text-sm font-medium transition-colors">
                Buka sesi sekarang →
            </a>
        </div>
    @endif
</x-simans-layout>