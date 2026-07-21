<x-simans-layout title="Rekap Ketidakhadiran - Prakerin">

    <div class="mb-5">
        <h1 class="text-xl font-bold text-white">Rekap Ketidakhadiran</h1>
        <p class="text-gray-400 text-sm mt-0.5">Laporan izin, sakit, dan libur siswa</p>
    </div>

    {{-- Sub-nav --}}
    <div class="flex gap-2 mb-5 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none">
        <a href="{{ route('guru.prakerin.index') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Dashboard</a>
        <a href="{{ route('guru.prakerin.locations') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">DU/DI Saya</a>
        <a href="{{ route('guru.prakerin.placements') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Penempatan Siswa</a>
        <a href="{{ route('guru.prakerin.izin') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-orange-600 text-white">Izin/Sakit/Libur</a>
        <a href="{{ route('guru.prakerin.recap.absensi') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Rekap Absensi</a>
        <a href="{{ route('guru.prakerin.recap.jurnal') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Rekap Jurnal</a>
    </div>

    {{-- Filter periode --}}
    @if ($periods->count() > 1)
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach ($periods as $p)
            <a href="{{ route('guru.prakerin.izin', ['period_id' => $p->id]) }}"
               class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors
               {{ $period?->id == $p->id ? 'bg-orange-600 text-white' : 'bg-gray-900 border border-white/10 text-gray-400 hover:text-white' }}">
                {{ $p->name }}
            </a>
        @endforeach
    </div>
    @endif

    @if (! $period)
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-10 text-center">
            <p class="text-gray-400 text-sm">Tidak ada periode aktif.</p>
        </div>
    @elseif ($absences->isEmpty())
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-10 text-center">
            <p class="text-gray-400 text-sm">Belum ada laporan ketidakhadiran.</p>
        </div>
    @else
        <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">Siswa</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">DU/DI</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">Tanggal</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">Jenis</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">Keterangan</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">Lampiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($absences as $abs)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-3">
                                <p class="text-white font-medium text-sm">{{ $abs->student->name }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-400 text-xs">{{ $abs->placement->location->name }}</td>
                            <td class="px-5 py-3 text-gray-300 text-xs whitespace-nowrap">
                                {{ $abs->absence_date->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-lg
                                    {{ $abs->type === 'sakit' ? 'bg-red-500/10 text-red-400 border border-red-500/20' :
                                       ($abs->type === 'libur' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' :
                                       'bg-orange-500/10 text-orange-400 border border-orange-500/20') }}">
                                    {{ $abs->type_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-400 text-xs max-w-xs">
                                {{ Str::limit($abs->reason, 60) }}
                            </td>
                            <td class="px-5 py-3">
                                @if ($abs->attachment_path)
                                    <a href="{{ Storage::url($abs->attachment_path) }}" target="_blank"
                                       class="text-blue-400 hover:text-blue-300 text-xs transition-colors">
                                        Lihat
                                    </a>
                                @else
                                    <span class="text-gray-700 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $absences->links() }}</div>
    @endif
</x-simans-layout>
