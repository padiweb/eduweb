<x-simans-layout title="Prakerin">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-white">Praktik Kerja Industri</h1>
        <p class="text-gray-400 text-sm mt-0.5">Absensi & Jurnal Harian</p>
    </div>

    @foreach (['success','error','info'] as $msg)
        @if (session($msg))
            <div class="mb-4 p-3 rounded-xl text-sm border
                {{ $msg === 'success' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' :
                   ($msg === 'error' ? 'bg-red-500/10 border-red-500/20 text-red-400' :
                   'bg-blue-500/10 border-blue-500/20 text-blue-400') }}">
                {{ session($msg) }}
            </div>
        @endif
    @endforeach

    @if (! $placement)
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-12 text-center">
            <p class="text-gray-400">Anda belum memiliki penempatan prakerin aktif hari ini.</p>
            <p class="text-gray-600 text-sm mt-1">Hubungi admin/wali kelas.</p>
        </div>
    @else
        {{-- Info DU/DI --}}
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-4 mb-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white font-semibold text-sm">{{ $placement->location->name }}</p>
                    @if ($placement->location->address)
                        <p class="text-gray-400 text-xs mt-0.5">{{ $placement->location->address }}</p>
                    @endif
                    <p class="text-gray-500 text-xs mt-1">
                        {{ $placement->period->name }}
                        @if ($placement->location->checkin_time)
                            · Masuk: {{ $placement->location->checkin_time }}
                        @endif
                        @if ($placement->location->checkout_time)
                            · Pulang: {{ $placement->location->checkout_time }}
                        @endif
                    </p>
                    @if ($placement->location->supervisors->count() > 0)
                        <p class="text-gray-600 text-xs mt-0.5">Pembimbing: {{ $placement->location->supervisors->pluck('name')->join(', ') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <p class="text-gray-400 text-xs font-medium uppercase tracking-wider mb-2">
            Hari ini — {{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}
        </p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
            {{-- Card Masuk --}}
            <div class="bg-gray-900 border {{ $checkin ? 'border-emerald-500/30' : ($absence ? 'border-white/5' : 'border-white/5') }} rounded-2xl p-4 text-center">
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl flex items-center justify-center {{ $checkin ? 'bg-emerald-500/15' : 'bg-gray-800' }}">
                    <svg class="w-5 h-5 {{ $checkin ? 'text-emerald-400' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-400 mb-1">Masuk</p>
                @if ($checkin)
                    <p class="text-emerald-400 text-xs font-bold">{{ $checkin->selfie_taken_at?->format('H:i') ?? $checkin->created_at->format('H:i') }}</p>
                    <p class="text-gray-600 text-xs">{{ $checkin->status_label }}</p>
                @elseif ($absence)
                    <p class="text-gray-600 text-xs mt-1">—</p>
                @else
                    <a href="{{ route('siswa.prakerin.absen', 'check_in') }}"
                       class="inline-block mt-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs rounded-lg font-semibold transition-colors">
                        Absen
                    </a>
                @endif
            </div>

            {{-- Card Pulang --}}
            <div class="bg-gray-900 border {{ $checkout ? 'border-blue-500/30' : 'border-white/5' }} rounded-2xl p-4 text-center">
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl flex items-center justify-center {{ $checkout ? 'bg-blue-500/15' : 'bg-gray-800' }}">
                    <svg class="w-5 h-5 {{ $checkout ? 'text-blue-400' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-400 mb-1">Pulang</p>
                @if ($checkout)
                    <p class="text-blue-400 text-xs font-bold">{{ $checkout->selfie_taken_at?->format('H:i') ?? $checkout->created_at->format('H:i') }}</p>
                @elseif ($absence)
                    <p class="text-gray-600 text-xs mt-1">—</p>
                @else
                    <a href="{{ route('siswa.prakerin.absen', 'check_out') }}"
                       class="inline-block mt-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs rounded-lg font-semibold transition-colors">
                        Absen
                    </a>
                @endif
            </div>

            {{-- Card Jurnal --}}
            <div class="bg-gray-900 border {{ $journal ? 'border-amber-500/30' : 'border-white/5' }} rounded-2xl p-4 text-center">
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl flex items-center justify-center {{ $journal ? 'bg-amber-500/15' : 'bg-gray-800' }}">
                    <svg class="w-5 h-5 {{ $journal ? 'text-amber-400' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-400 mb-1">Jurnal</p>
                @if ($journal)
                    <p class="text-amber-400 text-xs font-bold">{{ $journal->submitted_at?->format('H:i') ?? $journal->updated_at->format('H:i') }}</p>
                    <a href="{{ route('siswa.prakerin.jurnal') }}" class="text-gray-500 text-xs hover:text-amber-400 transition-colors">Edit</a>
                @else
                    <a href="{{ route('siswa.prakerin.jurnal') }}"
                       class="inline-block mt-1 px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs rounded-lg font-semibold transition-colors">
                        Isi
                    </a>
                @endif
            </div>

            {{-- Card Tidak Hadir --}}
            @php $absence = \App\Models\PrakerinAbsence::where('placement_id', $placement->id)->where('absence_date', $today)->first(); @endphp
            <div class="bg-gray-900 border {{ $absence ? 'border-orange-500/30' : 'border-white/5' }} rounded-2xl p-4 text-center">
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl flex items-center justify-center {{ $absence ? 'bg-orange-500/15' : 'bg-gray-800' }}">
                    <svg class="w-5 h-5 {{ $absence ? 'text-orange-400' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-400 mb-1">Tidak Hadir</p>
                @if ($absence)
                    <p class="text-orange-400 text-xs font-bold">{{ $absence->type_label }}</p>
                    <p class="text-emerald-400 text-xs">Tercatat</p>
                @else
                    @if (! $checkin)
                        <a href="{{ route('siswa.prakerin.izin') }}"
                           class="inline-block mt-1 px-3 py-1.5 bg-orange-700 hover:bg-orange-600 text-white text-xs rounded-lg font-semibold transition-colors">
                            Lapor
                        </a>
                    @else
                        <p class="text-gray-700 text-xs mt-1">—</p>
                    @endif
                @endif
            </div>
        </div>

        @if (! $journal)
            <div class="mb-4 p-3 rounded-xl bg-amber-500/5 border border-amber-500/15 flex items-center gap-3">
                <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-amber-300 text-xs">Jangan lupa isi jurnal harian! Batas waktu sampai <strong>23:59 malam ini</strong>.</p>
            </div>
        @endif

        @if ($recentLogs->count() > 0)
            <p class="text-gray-400 text-xs font-medium uppercase tracking-wider mb-2">7 Hari Terakhir</p>
            <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
                @foreach ($recentLogs as $date => $logs)
                    @php $ci = $logs->firstWhere('type','check_in'); $co = $logs->firstWhere('type','check_out'); @endphp
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-white/5 last:border-0">
                        <div class="w-20 flex-shrink-0">
                            <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($date)->translatedFormat('D, d M') }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-xs {{ $ci ? 'bg-emerald-500/10 text-emerald-400' : 'bg-gray-800 text-gray-600' }}">
                            {{ $ci ? 'Masuk '.($ci->selfie_taken_at?->format('H:i') ?? $ci->created_at->format('H:i')) : '— Masuk' }}
                        </span>
                        <span class="px-2 py-0.5 rounded-md text-xs {{ $co ? 'bg-blue-500/10 text-blue-400' : 'bg-gray-800 text-gray-600' }}">
                            {{ $co ? 'Pulang '.($co->selfie_taken_at?->format('H:i') ?? $co->created_at->format('H:i')) : '— Pulang' }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-4 text-center">
            <a href="{{ route('siswa.prakerin.jurnal.history') }}" class="text-gray-500 text-sm hover:text-white transition-colors">
                Lihat semua jurnal →
            </a>
        </div>
    @endif

</x-simans-layout>
