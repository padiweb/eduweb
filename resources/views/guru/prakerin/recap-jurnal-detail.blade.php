<x-simans-layout title="Detail Jurnal - {{ $placement->student->name }}">

    <div class="mb-4">
        <a href="{{ route('guru.prakerin.recap.jurnal', ['period_id' => $placement->period_id, 'location_id' => $placement->location_id]) }}"
           class="text-gray-400 text-sm hover:text-gray-900 flex items-center gap-1 mb-3 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">{{ $placement->student->name }}</h1>
                <p class="text-gray-500 text-xs mt-0.5">{{ $placement->location->name }} · {{ $placement->period->name }}</p>
            </div>
            <div class="text-right">
                <p class="text-xl font-bold {{ $filled < $total ? 'text-red-400' : 'text-blue-600' }}">{{ $filled }}/{{ $total }}</p>
                <p class="text-gray-400 text-xs">jurnal terisi</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-3 p-3 rounded-xl bg-blue-600/10 border border-blue-200 text-blue-600 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Progress --}}
    @php $pct = $total > 0 ? round($filled / $total * 100) : 0; @endphp
    <div class="flex items-center gap-3 mb-5">
        <div class="flex-1 bg-white rounded-full h-1.5">
            <div class="h-1.5 rounded-full {{ $pct >= 80 ? 'bg-blue-600' : ($pct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                 style="width:{{ $pct }}%"></div>
        </div>
        <span class="text-xs font-semibold flex-shrink-0 {{ $pct >= 80 ? 'text-blue-600' : ($pct >= 50 ? 'text-amber-400' : 'text-red-400') }}">{{ $pct }}%</span>
    </div>

    {{-- Daftar hari — terbaru di atas, accordion --}}
    <div x-data="{ open: null }" class="space-y-2">
        @foreach (array_reverse($days, true) as $key => $day)
            @php
                $journal    = $day['journal'];
                $absence    = $day['absence'];
                $attendance = $day['attendance'];
                $isPast     = $day['date']->lt(today());
                $isToday    = $day['date']->isToday();
                $isFuture   = $day['date']->gt(today());
            @endphp

            {{-- Hari mendatang — tidak tampil --}}
            @if ($isFuture)
                @continue
            @endif

            @php
                // Warna border & badge
                if ($journal) {
                    $border = 'border-amber-500/25';
                    $badgeClass = 'bg-amber-500/10 border-amber-500/20 text-amber-400';
                    $badgeText  = 'Terisi';
                    $dotColor   = 'bg-amber-500';
                } elseif ($absence) {
                    $border = 'border-orange-400/20';
                    $badgeClass = 'bg-orange-500/10 border-orange-400/20 text-orange-400';
                    $badgeText  = $absence->type_label;
                    $dotColor   = 'bg-orange-400';
                } elseif ($isPast) {
                    $border = 'border-red-500/20';
                    $badgeClass = 'bg-red-500/10 border-red-500/20 text-red-400';
                    $badgeText  = 'Tidak Diisi';
                    $dotColor   = 'bg-red-500';
                } else {
                    $border = 'border-blue-500/20';
                    $badgeClass = 'bg-blue-500/10 border-blue-500/20 text-blue-400';
                    $badgeText  = 'Hari Ini';
                    $dotColor   = 'bg-blue-500';
                }
            @endphp

            <div class="border {{ $border }} rounded-xl overflow-hidden bg-white">
                {{-- Header accordion --}}
                <button type="button"
                        @click="open = (open === '{{ $key }}') ? null : '{{ $key }}'"
                        class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full {{ $dotColor }} flex-shrink-0"></span>
                        <div>
                            <span class="text-gray-700 text-sm font-medium">
                                {{ $day['date']->translatedFormat('D, d M Y') }}
                                @if ($isToday) <span class="text-blue-400 text-xs ml-1">Hari ini</span> @endif
                            </span>
                            @if ($journal)
                                <p class="text-gray-400 text-xs">
                                    {{ $journal->submitted_at?->format('H:i') ?? '' }}
                                    @if ($journal->photos->count() > 0)
                                        · {{ $journal->photos->count() }} foto
                                    @endif
                                </p>
                            @elseif ($absence)
                                <p class="text-orange-400/70 text-xs">{{ $absence->reason }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="px-2 py-0.5 text-xs rounded-lg border {{ $badgeClass }}">{{ $badgeText }}</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform"
                             :class="open === '{{ $key }}' ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                {{-- Konten accordion --}}
                <div x-show="open === '{{ $key }}'"
                     x-cloak
                     class="border-t border-gray-200">
                    @if ($journal)
                        <div class="px-4 py-4 space-y-4">
                            {{-- Isi jurnal --}}
                            <div>
                                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-2">Laporan Kegiatan</p>
                                <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">{{ $journal->content }}</p>
                            </div>

                            {{-- Foto --}}
                            @if ($journal->photos->count() > 0)
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-2">
                                        Foto Dokumentasi ({{ $journal->photos->count() }})
                                    </p>
                                    <div class="flex gap-2 overflow-x-auto pb-1">
                                        @foreach ($journal->photos as $photo)
                                            <a href="{{ Storage::url($photo->photo_path) }}" target="_blank" class="flex-shrink-0">
                                                <img src="{{ Storage::url($photo->photo_path) }}"
                                                     alt="{{ $photo->caption ?? 'foto' }}"
                                                     style="width:80px;height:80px;object-fit:cover"
                                                     class="rounded-xl border border-gray-200 hover:border-amber-400/50 transition-colors"/>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Catatan guru --}}
                            <div class="pt-3 border-t border-gray-200">
                                @if ($journal->teacher_note)
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Catatan Pembimbing</p>
                                    <div class="p-3 bg-blue-500/5 border border-blue-500/15 rounded-xl">
                                        <p class="text-blue-300 text-sm">{{ $journal->teacher_note }}</p>
                                        <p class="text-gray-400 text-xs mt-1">{{ $journal->noted_at?->translatedFormat('d M Y, H:i') }}</p>
                                    </div>
                                @else
                                    <form action="{{ route('guru.prakerin.journal.note', $journal) }}" method="POST" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="teacher_note"
                                               placeholder="Tulis catatan untuk jurnal ini..."
                                               class="flex-1 bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-400 placeholder-gray-400 min-w-0">
                                        <button type="submit"
                                                class="px-3 py-2 bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 text-sm rounded-xl border border-blue-500/20 transition-colors whitespace-nowrap">
                                            Simpan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                    @elseif ($absence)
                        <div class="px-4 py-4">
                            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Keterangan</p>
                            <p class="text-gray-600 text-sm">{{ $absence->reason }}</p>
                            <p class="text-gray-400 text-xs mt-2">Jurnal tidak wajib diisi saat {{ strtolower($absence->type_label) }}.</p>
                        </div>

                    @else
                        <div class="px-4 py-4 text-center">
                            <p class="text-gray-400 text-sm">
                                {{ $isPast ? 'Siswa tidak mengisi jurnal pada hari ini.' : 'Belum ada jurnal.' }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</x-simans-layout>
