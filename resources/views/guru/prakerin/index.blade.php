<x-simans-layout title="Prakerin - Koordinator">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Prakerin</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola DU/DI dan penempatan siswa di lokasi yang Anda bimbing</p>
    </div>

    {{-- Sub-nav --}}
    <div class="flex gap-2 mb-5 flex-wrap">
        <a href="{{ route('guru.prakerin.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-blue-600 text-gray-900">Dashboard</a>
        <a href="{{ route('guru.prakerin.locations') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">DU/DI Saya</a>
        <a href="{{ route('guru.prakerin.placements') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Penempatan Siswa</a>
        <a href="{{ route('guru.prakerin.recap.absensi') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Rekap Absensi</a>
        <a href="{{ route('guru.prakerin.recap.jurnal') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Rekap Jurnal</a>
    </div>

    {{-- Filter periode --}}
    @if ($periods->count() > 1)
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach ($periods as $p)
            <a href="{{ route('guru.prakerin.index', ['period_id' => $p->id]) }}"
               class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2
               {{ $period?->id == $p->id ? 'bg-blue-600 text-gray-900' : 'bg-white border border-gray-200 text-gray-500 hover:text-gray-900' }}">
                {{ $p->name }}
                @if ($p->isOngoing()) <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span> @endif
            </a>
        @endforeach
    </div>
    @endif

    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl bg-blue-600/10 border border-blue-200 text-blue-600 text-sm">{{ session('success') }}</div>
    @endif

    @if ($periods->isEmpty())
        <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center">
            <p class="text-gray-500">Anda belum ditunjuk sebagai koordinator prakerin di periode manapun.</p>
            <p class="text-gray-400 text-sm mt-1">Hubungi admin untuk ditambahkan sebagai koordinator.</p>
        </div>
    @elseif (! $period)
        <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center">
            <p class="text-gray-500">Pilih periode di atas.</p>
        </div>
    @else
        {{-- Info periode --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-4 mb-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-900 font-semibold">{{ $period->name }}</p>
                    <p class="text-gray-500 text-sm mt-0.5">
                        {{ $period->start_date->format('d M Y') }} – {{ $period->end_date->format('d M Y') }}
                    </p>
                </div>
                @php
                    $statusColor = match($period->status_label) {
                        'Berlangsung' => 'bg-blue-600/10 text-blue-600 border-blue-200',
                        'Belum Mulai' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                        default       => 'bg-gray-100 text-gray-500 border-gray-200',
                    };
                @endphp
                <span class="px-3 py-1 rounded-xl text-xs font-semibold border {{ $statusColor }}">{{ $period->status_label }}</span>
            </div>
        </div>

        {{-- Lokasi yang dibimbing --}}
        @if ($myLocations->isEmpty())
            <div class="bg-amber-500/5 border border-amber-500/20 rounded-2xl p-5 mb-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-amber-300 text-sm font-medium">Belum ada DU/DI yang Anda bimbing</p>
                    <p class="text-amber-400/70 text-xs mt-0.5">Tambahkan DU/DI di menu <a href="{{ route('guru.prakerin.locations', ['period_id' => $period->id]) }}" class="underline">DU/DI Saya</a>.</p>
                </div>
            </div>
        @else
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider mb-3">DU/DI yang Anda bimbing</p>
            <div class="space-y-3 mb-6">
                @foreach ($myLocations as $loc)
                    <div class="bg-white border border-gray-200 rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <p class="text-gray-900 font-semibold">{{ $loc->name }}</p>
                                @if ($loc->address)
                                    <p class="text-gray-400 text-xs mt-0.5">{{ $loc->address }}</p>
                                @endif
                                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-gray-400">
                                    @if ($loc->checkin_time)
                                        <span>Masuk: <span class="text-gray-600">{{ $loc->checkin_time }}</span>
                                        @if ($loc->checkin_late_after) · Toleransi: <span class="text-gray-600">{{ $loc->checkin_late_after }}</span> @endif
                                        </span>
                                    @endif
                                    @if ($loc->checkout_time)
                                        <span>Pulang: <span class="text-gray-600">{{ $loc->checkout_time }}</span></span>
                                    @endif
                                    <span>Siswa: <span class="text-gray-900 font-medium">{{ $loc->placements->count() }}</span></span>
                                </div>
                            </div>
                            <div class="flex gap-2 flex-shrink-0">
                                <a href="{{ route('guru.prakerin.placements', ['period_id' => $period->id, 'location_id' => $loc->id]) }}"
                                   class="px-3 py-1.5 bg-white hover:bg-gray-100 border border-gray-200 text-gray-600 text-xs rounded-lg transition-colors">
                                    Siswa
                                </a>
                            </div>
                        </div>
                        {{-- Daftar siswa ringkas --}}
                        @if ($loc->placements->count() > 0)
                            <div class="mt-3 pt-3 border-t border-gray-200 flex flex-wrap gap-1">
                                @foreach ($loc->placements->take(8) as $p)
                                    <span class="px-2 py-0.5 bg-white text-gray-600 text-xs rounded-lg">{{ $p->student->name }}</span>
                                @endforeach
                                @if ($loc->placements->count() > 8)
                                    <span class="text-gray-400 text-xs px-2 py-0.5">+{{ $loc->placements->count() - 8 }} lainnya</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Quick links --}}
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('guru.prakerin.locations', ['period_id' => $period->id]) }}"
               class="bg-white border border-gray-200 hover:border-blue-200 rounded-2xl p-4 transition-colors group">
                <div class="w-9 h-9 rounded-xl bg-blue-600/10 flex items-center justify-center mb-2 group-hover:bg-blue-600/20 transition-colors">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </div>
                <p class="text-gray-900 text-sm font-medium">Tambah DU/DI</p>
                <p class="text-gray-400 text-xs mt-0.5">Input lokasi baru</p>
            </a>
            <a href="{{ route('guru.prakerin.placements', ['period_id' => $period->id]) }}"
               class="bg-white border border-gray-200 hover:border-blue-500/30 rounded-2xl p-4 transition-colors group">
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center mb-2 group-hover:bg-blue-500/20 transition-colors">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                </div>
                <p class="text-gray-900 text-sm font-medium">Penempatan Siswa</p>
                <p class="text-gray-400 text-xs mt-0.5">Assign siswa ke DU/DI</p>
            </a>
        </div>
    @endif

</x-simans-layout>
