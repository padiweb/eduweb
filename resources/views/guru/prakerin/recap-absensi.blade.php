<x-simans-layout title="Rekap Absensi Prakerin">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Rekap Absensi Prakerin</h1>
        <p class="text-gray-500 text-sm mt-1">Ringkasan kehadiran semua siswa prakerin</p>
    </div>

    {{-- Sub-nav --}}
    <div class="tab-nav-scroll">
        <a href="{{ route('guru.prakerin.index') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Dashboard</a>
        <a href="{{ route('guru.prakerin.locations') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">DU/DI Saya</a>
        <a href="{{ route('guru.prakerin.placements') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Penempatan Siswa</a>
        <a href="{{ route('guru.prakerin.izin') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Izin/Sakit/Libur</a>
        <a href="{{ route('guru.prakerin.recap.absensi') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-blue-600 text-white">Rekap Absensi</a>
        <a href="{{ route('guru.prakerin.recap.jurnal') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Rekap Jurnal</a>
    </div>

    {{-- Filter periode --}}
    <div class="tab-nav-scroll">
        @foreach ($periods as $p)
            <a href="{{ route('guru.prakerin.recap.absensi', ['period_id' => $p->id]) }}"
               class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors
               {{ $period?->id == $p->id ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-500 hover:text-gray-700' }}">
                {{ $p->name }}
            </a>
        @endforeach
        @if ($locations->count() > 0)
        <select onchange="window.location='{{ route('guru.prakerin.recap.absensi') }}?period_id={{ $period?->id }}&location_id='+this.value"
                class="bg-white border border-gray-200 text-gray-600 rounded-xl px-3 py-1.5 text-sm focus:outline-none">
            <option value="">Semua DU/DI</option>
            @foreach ($locations as $loc)
                <option value="{{ $loc->id }}" {{ $locId == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
            @endforeach
        </select>
        @endif
    </div>

    @if ($placements->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-500">Tidak ada data untuk filter ini.</p>
        </div>
    @else
        <div class="tbl-card"><div class="tbl-wrap">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Siswa</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">DU/DI</th>
                        <th class="text-center text-xs text-gray-500 font-medium px-3 py-3">Hari</th>
                        <th class="text-center text-xs text-blue-600 font-medium px-3 py-3">Hadir</th>
                        <th class="text-center text-xs text-amber-500 font-medium px-3 py-3">Terlambat</th>
                        <th class="text-center text-xs text-orange-600 font-medium px-3 py-3">Izin</th>
                        <th class="text-center text-xs text-red-600 font-medium px-3 py-3">Sakit</th>
                        <th class="text-center text-xs text-blue-600 font-medium px-3 py-3">Libur</th>
                        <th class="text-center text-xs text-red-500 font-medium px-3 py-3">Alfa</th>
                        <th class="text-center text-xs text-amber-600 font-medium px-3 py-3">Jurnal</th>
                        <th class="text-center text-xs text-gray-500 font-medium px-3 py-3">%</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($placements as $p)
                        @php
                            $s     = $stats[$p->id];
                            $izin  = $s['izin']  ?? 0;
                            $sakit = $s['sakit'] ?? 0;
                            $libur = $s['libur'] ?? 0;
                            $alfa  = max(0, $s['total_days'] - $s['hadir'] - $izin - $sakit - $libur);
                            $pct   = $s['total_days'] > 0 ? round($s['hadir'] / $s['total_days'] * 100) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3"><p class="text-gray-900 font-medium">{{ $p->student->name }}</p></td>
                            <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $p->location->name }}</td>
                            <td class="px-3 py-3 text-center text-gray-600">{{ $s['total_days'] }}</td>
                            <td class="px-3 py-3 text-center text-blue-600 font-semibold">{{ $s['hadir'] }}</td>
                            <td class="px-3 py-3 text-center text-amber-600">{{ $s['terlambat'] }}</td>
                            <td class="px-3 py-3 text-center text-orange-600">{{ $izin }}</td>
                            <td class="px-3 py-3 text-center text-red-600">{{ $sakit }}</td>
                            <td class="px-3 py-3 text-center text-blue-600">{{ $libur }}</td>
                            <td class="px-3 py-3 text-center text-red-500 font-semibold">{{ $alfa }}</td>
                            <td class="px-3 py-3 text-center text-amber-600">{{ $s['jurnal'] }}</td>
                            <td class="px-3 py-3 text-center">
                                <span class="text-xs font-semibold {{ $pct >= 80 ? 'text-blue-600' : ($pct >= 60 ? 'text-amber-600' : 'text-red-600') }}">
                                    {{ $pct }}%
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <a href="{{ route('guru.prakerin.placements.show', $p) }}"
                                   class="px-3 py-1.5 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-200 text-gray-600 hover:text-blue-600 text-xs rounded-lg transition-all font-medium">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</x-simans-layout>
