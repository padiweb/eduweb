<x-simans-layout title="Rekap Jurnal Prakerin">

    <div class="mb-5">
        <h1 class="text-xl font-bold text-white">Rekap Jurnal Prakerin</h1>
        <p class="text-gray-400 text-sm mt-1">Jurnal harian siswa per DU/DI</p>
    </div>

    {{-- Sub-nav --}}
    <div class="flex gap-2 mb-5 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none">
        <a href="{{ route('guru.prakerin.index') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Dashboard</a>
        <a href="{{ route('guru.prakerin.locations') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">DU/DI Saya</a>
        <a href="{{ route('guru.prakerin.placements') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Penempatan Siswa</a>
        <a href="{{ route('guru.prakerin.izin') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Izin/Sakit/Libur</a>
        <a href="{{ route('guru.prakerin.recap.absensi') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Rekap Absensi</a>
        <a href="{{ route('guru.prakerin.recap.jurnal') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-emerald-500 text-white">Rekap Jurnal</a>
    </div>

    {{-- Filter periode --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach ($periods as $p)
            <a href="{{ route('guru.prakerin.recap.jurnal', ['period_id' => $p->id]) }}"
               class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors
               {{ $period?->id == $p->id ? 'bg-emerald-500 text-white' : 'bg-gray-900 border border-white/10 text-gray-400 hover:text-white' }}">
                {{ $p->name }}
            </a>
        @endforeach
        @if ($locations->count() > 0)
            <select onchange="window.location='{{ route('guru.prakerin.recap.jurnal') }}?period_id={{ $period?->id }}&location_id='+this.value"
                    class="bg-gray-900 border border-white/10 text-gray-300 rounded-xl px-3 py-1.5 text-sm focus:outline-none">
                <option value="">Semua DU/DI</option>
                @foreach ($locations as $loc)
                    <option value="{{ $loc->id }}" {{ $locId == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    @if (! $period)
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-10 text-center">
            <p class="text-gray-400 text-sm">Tidak ada periode aktif.</p>
        </div>
    @elseif ($placements->isEmpty())
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-10 text-center">
            <p class="text-gray-400 text-sm">Tidak ada data penempatan siswa.</p>
        </div>
    @else
        {{-- Rekap per siswa --}}
        <div class="space-y-5">
            @foreach ($placements as $placement)
                @php
                    $start   = $placement->start_date ?? $period->start_date;
                    $end     = $placement->end_date   ?? $period->end_date;
                    $endShow = $end->lte(today()) ? $end : today();

                    // Index jurnal by date
                    $jurnalByDate = $placement->journals->keyBy(fn($j) => $j->journal_date->format('Y-m-d'));
                    $absenceByDate = $placement->absences
                        ->where('status', 'approved')
                        ->keyBy(fn($a) => $a->absence_date->format('Y-m-d'));
                    $totalHari = $start->diffInDays($endShow) + 1;
                    $jumlahJurnal = $jurnalByDate->count();
                    $belumJurnal  = $totalHari - $jumlahJurnal;
                @endphp

                <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
                    {{-- Header siswa --}}
                    <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                        <div>
                            <p class="text-white font-semibold">{{ $placement->student->name }}</p>
                            <p class="text-gray-500 text-xs mt-0.5">{{ $placement->location->name }}</p>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="text-gray-500">Jurnal: <span class="text-amber-400 font-semibold">{{ $jumlahJurnal }}</span>/{{ $totalHari }}</span>
                            @if ($belumJurnal > 0)
                                <span class="px-2 py-0.5 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg">
                                    {{ $belumJurnal }} hari belum diisi
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg">Lengkap</span>
                            @endif
                        </div>
                    </div>

                    {{-- Tabel per hari --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-white/5 bg-white/[0.02]">
                                    <th class="text-left text-gray-500 font-medium px-5 py-2.5">Tanggal</th>
                                    <th class="text-center text-gray-500 font-medium px-4 py-2.5">Absensi</th>
                                    <th class="text-left text-gray-500 font-medium px-4 py-2.5">Jurnal</th>
                                    <th class="text-left text-gray-500 font-medium px-4 py-2.5">Catatan Guru</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.03]">
                                @php $current = $start->copy(); @endphp
                                @while ($current->lte($endShow))
                                    @php
                                        $key     = $current->format('Y-m-d');
                                        $journal = $jurnalByDate->get($key);
                                        $absence = $absenceByDate->get($key);
                                        $isToday = $current->isToday();
                                    @endphp
                                    <tr class="{{ $isToday ? 'bg-white/[0.03]' : 'hover:bg-white/[0.01]' }} transition-colors">
                                        <td class="px-5 py-2.5">
                                            <p class="text-gray-300 font-medium">{{ $current->translatedFormat('D, d M Y') }}</p>
                                            @if ($isToday) <p class="text-emerald-400 text-xs">Hari ini</p> @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            @if ($absence)
                                                <span class="px-2 py-0.5 rounded-md
                                                    {{ $absence->type === 'sakit' ? 'bg-red-500/10 text-red-400' :
                                                       ($absence->type === 'libur' ? 'bg-blue-500/10 text-blue-400' :
                                                       'bg-orange-500/10 text-orange-400') }}">
                                                    {{ $absence->type_label }}
                                                </span>
                                            @else
                                                @php
                                                    $checkin = $placement->attendances
                                                        ->where('attendance_date', $key)
                                                        ->where('type', 'check_in')
                                                        ->first();
                                                @endphp
                                                @if ($checkin)
                                                    <span class="text-emerald-400">{{ $checkin->selfie_taken_at?->format('H:i') ?? 'Hadir' }}</span>
                                                @elseif ($current->lt(today()))
                                                    <span class="text-red-400">Alfa</span>
                                                @else
                                                    <span class="text-gray-600">—</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5">
                                            @if ($journal)
                                                <p class="text-gray-300 leading-relaxed">{{ Str::limit($journal->content, 80) }}</p>
                                                @if ($journal->photos->count() > 0)
                                                    <p class="text-gray-600 mt-1">{{ $journal->photos->count() }} foto</p>
                                                @endif
                                            @elseif ($current->lt(today()))
                                                <span class="text-red-400">Tidak diisi</span>
                                            @elseif ($isToday)
                                                <span class="text-amber-400">Belum diisi</span>
                                            @else
                                                <span class="text-gray-700">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5">
                                            @if ($journal?->teacher_note)
                                                <p class="text-blue-300">{{ Str::limit($journal->teacher_note, 60) }}</p>
                                            @elseif ($journal)
                                                <form action="{{ route('guru.prakerin.journal.note', $journal) }}" method="POST" class="flex gap-1.5">
                                                    @csrf
                                                    <input type="text" name="teacher_note" placeholder="Tulis catatan..."
                                                           class="flex-1 bg-gray-800 border border-white/10 text-white rounded-lg px-2 py-1 text-xs focus:outline-none focus:border-blue-500/50 placeholder-gray-600 min-w-0">
                                                    <button type="submit" class="px-2 py-1 bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 text-xs rounded-lg border border-blue-500/20 transition-colors whitespace-nowrap">
                                                        Simpan
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-700">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @php $current->addDay(); @endphp
                                @endwhile
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-simans-layout>
