<x-simans-layout title="Dashboard">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Total Kelas</p>
            <p class="text-3xl font-bold text-white">{{ $stats['total_kelas'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Semester aktif</p>
        </div>
        <div class="bg-gray-900 border border-emerald-500/20 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Hadir Hari Ini</p>
            <p class="text-3xl font-bold text-emerald-400">{{ $stats['total_hadir'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Semua kelas</p>
        </div>
        <div class="bg-gray-900 border border-red-500/15 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Alfa Hari Ini</p>
            <p class="text-3xl font-bold text-red-400">{{ $stats['total_alfa'] }}</p>
            <p class="text-xs text-gray-600 mt-1">Tanpa keterangan</p>
        </div>
        <div class="bg-gray-900 border border-amber-500/15 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Jurnal Bulan Ini</p>
            <p class="text-3xl font-bold text-amber-400">{{ $jurnalBulanIni }}</p>
            <p class="text-xs text-gray-600 mt-1">Jurnal mengajar</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Status absensi kelas --}}
        <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                <h2 class="text-sm font-semibold text-white">Status Kelas Hari Ini</h2>
                <a href="{{ route('guru.attendance.index') }}" class="text-xs text-emerald-400 hover:text-emerald-300 transition-colors">Kelola →</a>
            </div>
            @forelse($classrooms as $classroom)
                @php $session = $todaySessions->firstWhere('classroom_id', $classroom->id); @endphp
                <div class="flex items-center gap-3 px-5 py-3 border-b border-white/[0.03] last:border-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white">{{ $classroom->name }}</p>
                        <p class="text-xs text-gray-500">{{ $classroom->major->code ?? '' }} · {{ $classroom->students->count() }} siswa</p>
                    </div>
                    @if($session)
                        <span class="text-xs text-gray-400">{{ $session->attendances->count() }} absen</span>
                        @if($session->is_closed)
                            <span class="text-xs px-2 py-0.5 rounded-lg bg-gray-800 text-gray-500 border border-white/5">Tutup</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Aktif
                            </span>
                        @endif
                    @else
                        <form method="POST" action="{{ route('guru.attendance.open') }}">
                            @csrf
                            <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                            <button type="submit" class="text-xs bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                Buka
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="px-5 py-8 text-center text-gray-500 text-sm">Belum ada kelas aktif.</div>
            @endforelse
        </div>

        {{-- Quick access --}}
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
            <h2 class="text-sm font-semibold text-white mb-4">Akses Cepat</h2>
            <div class="space-y-2">
                <a href="{{ route('guru.journal.index') }}" class="flex items-center gap-3 p-3 bg-gray-800/50 hover:bg-gray-800 border border-white/5 hover:border-white/10 rounded-xl transition-colors group">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-emerald-400 transition-colors flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                    <span class="text-sm text-gray-400 group-hover:text-white transition-colors">Jurnal Mengajar</span>
                </a>
                <a href="{{ route('guru.assignments.index') }}" class="flex items-center gap-3 p-3 bg-gray-800/50 hover:bg-gray-800 border border-white/5 hover:border-white/10 rounded-xl transition-colors group">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-emerald-400 transition-colors flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                    <span class="text-sm text-gray-400 group-hover:text-white transition-colors">Tugas & Nilai</span>
                </a>
                @if($prakerinCount > 0)
                    <a href="{{ route('guru.prakerin.index') }}" class="flex items-center gap-3 p-3 bg-amber-500/5 hover:bg-amber-500/10 border border-amber-500/15 hover:border-amber-500/25 rounded-xl transition-colors group">
                        <svg class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        <div class="flex-1">
                            <span class="text-sm text-amber-300 group-hover:text-amber-200 transition-colors">Koordinator Prakerin</span>
                            <p class="text-xs text-amber-400/60">{{ $prakerinCount }} siswa bimbingan</p>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-simans-layout>
