<x-simans-layout title="Rekap Absensi Prakerin">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Rekap Absensi Prakerin</h1>
        <p class="text-gray-500 text-sm mt-1">Ringkasan kehadiran semua siswa prakerin</p>
    </div>

    {{-- Sub-nav --}}
    <div class="flex gap-2 mb-5 flex-wrap">
        <a href="{{ route('admin.prakerin.periods.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Periode</a>
        <a href="{{ route('admin.prakerin.locations.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">DU/DI</a>
        <a href="{{ route('admin.prakerin.placements.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Penempatan Siswa</a>
        <a href="{{ route('admin.prakerin.recap.absensi') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-blue-600 text-white">Rekap Absensi</a>
        <a href="{{ route('admin.prakerin.recap.jurnal') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Rekap Jurnal</a>
    </div>

    {{-- Filter --}}
    <div class="flex flex-wrap gap-3 mb-5">
        <div class="flex flex-wrap gap-2">
            @foreach ($periods as $p)
                <a href="{{ route('admin.prakerin.recap.absensi', ['period_id' => $p->id]) }}"
                   class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors
                   {{ $periodId == $p->id ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-500 hover:text-white' }}">
                    {{ $p->name }}
                </a>
            @endforeach
        </div>
        @if ($locations->count() > 0)
        <select onchange="window.location='{{ route('admin.prakerin.recap.absensi') }}?period_id={{ $periodId }}&location_id='+this.value"
                class="bg-white border border-gray-200 text-gray-400 rounded-xl px-3 py-1.5 text-sm focus:outline-none">
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
        <div class="bg-white border border-gray-200 rounded-xl overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Siswa</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">DU/DI</th>
                        <th class="text-center text-xs text-gray-500 font-medium px-3 py-3">Hari</th>
                        <th class="text-center text-xs text-blue-600 font-medium px-3 py-3">Hadir</th>
                        <th class="text-center text-xs text-amber-500 font-medium px-3 py-3">Terlambat</th>
                        <th class="text-center text-xs text-orange-400 font-medium px-3 py-3">Izin</th>
                        <th class="text-center text-xs text-red-400 font-medium px-3 py-3">Sakit</th>
                        <th class="text-center text-xs text-blue-400 font-medium px-3 py-3">Libur</th>
                        <th class="text-center text-xs text-red-500 font-medium px-3 py-3">Alfa</th>
                        <th class="text-center text-xs text-amber-400 font-medium px-3 py-3">Jurnal</th>
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
                            <td class="px-3 py-3 text-center text-gray-400">{{ $s['total_days'] }}</td>
                            <td class="px-3 py-3 text-center text-blue-600 font-semibold">{{ $s['hadir'] }}</td>
                            <td class="px-3 py-3 text-center text-amber-400">{{ $s['terlambat'] }}</td>
                            <td class="px-3 py-3 text-center text-orange-400">{{ $izin }}</td>
                            <td class="px-3 py-3 text-center text-red-400">{{ $sakit }}</td>
                            <td class="px-3 py-3 text-center text-blue-400">{{ $libur }}</td>
                            <td class="px-3 py-3 text-center text-red-500 font-semibold">{{ $alfa }}</td>
                            <td class="px-3 py-3 text-center text-amber-400">{{ $s['jurnal'] }}</td>
                            <td class="px-3 py-3 text-center">
                                <span class="text-xs font-semibold {{ $pct >= 80 ? 'text-blue-600' : ($pct >= 60 ? 'text-amber-400' : 'text-red-400') }}">
                                    {{ $pct }}%
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <a href="{{ route('admin.prakerin.placements.show', $p) }}"
                                   class="px-3 py-1.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-400 text-xs rounded-lg transition-colors">
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
