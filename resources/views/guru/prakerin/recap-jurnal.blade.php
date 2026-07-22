<x-simans-layout title="Rekap Jurnal Prakerin">

    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">Rekap Jurnal Prakerin</h1>
        <p class="text-gray-500 text-sm mt-1">Pilih DU/DI lalu pilih siswa untuk melihat detail jurnal</p>
    </div>

    {{-- Sub-nav --}}
    <div class="flex gap-2 mb-5 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none">
        <a href="{{ route('guru.prakerin.index') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Dashboard</a>
        <a href="{{ route('guru.prakerin.locations') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">DU/DI Saya</a>
        <a href="{{ route('guru.prakerin.placements') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Penempatan Siswa</a>
        <a href="{{ route('guru.prakerin.izin') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Izin/Sakit/Libur</a>
        <a href="{{ route('guru.prakerin.recap.absensi') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Rekap Absensi</a>
        <a href="{{ route('guru.prakerin.recap.jurnal') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-blue-600 text-white">Rekap Jurnal</a>
    </div>

    {{-- Step 1: Pilih periode --}}
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach ($periods as $p)
            <a href="{{ route('guru.prakerin.recap.jurnal', ['period_id' => $p->id]) }}"
               class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors
               {{ $period?->id == $p->id ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-500 hover:text-white' }}">
                {{ $p->name }}
            </a>
        @endforeach
    </div>

    @if (! $period)
        <div class="bg-white border border-gray-200 rounded-xl p-10 text-center">
            <p class="text-gray-500 text-sm">Tidak ada periode aktif.</p>
        </div>
    @elseif ($locations->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-10 text-center">
            <p class="text-gray-500 text-sm">Belum ada DU/DI yang Anda bimbing.</p>
        </div>
    @else
        {{-- Step 2: Pilih DU/DI --}}
        <div class="mb-5">
            <label class="block text-xs text-gray-500 mb-2 font-medium uppercase tracking-wider">Pilih DU/DI</label>
            <select onchange="window.location='{{ route('guru.prakerin.recap.jurnal') }}?period_id={{ $period->id }}&location_id='+this.value"
                    class="w-full sm:w-auto bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-300">
                <option value="">— Pilih DU/DI —</option>
                @foreach ($locations as $loc)
                    <option value="{{ $loc->id }}" {{ $locId == $loc->id ? 'selected' : '' }}>
                        {{ $loc->name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if (! $locId)
            {{-- Belum pilih DU/DI --}}
            <div class="bg-white border border-gray-200 rounded-xl p-10 text-center">
                <svg class="w-10 h-10 text-gray-900 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                </svg>
                <p class="text-gray-500 text-sm">Pilih DU/DI di atas untuk melihat daftar siswa</p>
            </div>
        @elseif ($placements->isEmpty())
            <div class="bg-white border border-gray-200 rounded-xl p-10 text-center">
                <p class="text-gray-500 text-sm">Belum ada siswa di DU/DI ini.</p>
            </div>
        @else
            {{-- Step 3: Daftar siswa --}}
            <p class="text-xs text-gray-500 mb-3 font-medium uppercase tracking-wider">
                Pilih Siswa — {{ $placements->count() }} siswa
            </p>
            <div class="space-y-2">
                @foreach ($placements as $p)
                    @php $pct = $p->total_days > 0 ? round($p->filled_days / $p->total_days * 100) : 0; @endphp
                    <a href="{{ route('guru.prakerin.recap.jurnal.detail', $p) }}"
                       class="flex items-center gap-4 bg-white border border-gray-200 hover:border-amber-500/30 rounded-xl px-5 py-4 transition-colors group">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-900 font-semibold text-sm group-hover:text-amber-400 transition-colors">
                                {{ $p->student->name }}
                            </p>
                            <div class="flex items-center gap-3 mt-1.5">
                                {{-- Progress bar --}}
                                <div class="flex-1 max-w-32 bg-white rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $pct >= 80 ? 'bg-blue-600' : ($pct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">
                                    <span class="text-gray-400 font-medium">{{ $p->filled_days }}</span>/{{ $p->total_days }} hari
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if ($p->empty_days > 0)
                                <span class="px-2.5 py-1 bg-red-500/10 border border-red-500/20 text-red-400 text-xs rounded-lg">
                                    {{ $p->empty_days }} belum diisi
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-blue-600/10 border border-blue-200 text-blue-600 text-xs rounded-lg">
                                    Lengkap
                                </span>
                            @endif
                            <svg class="w-4 h-4 text-gray-500 group-hover:text-amber-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</x-simans-layout>
