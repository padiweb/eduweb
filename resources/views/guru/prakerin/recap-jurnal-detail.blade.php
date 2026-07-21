<x-simans-layout title="Detail Jurnal - {{ $placement->student->name }}">

    <div class="mb-5">
        <a href="{{ route('guru.prakerin.recap.jurnal', ['period_id' => $placement->period_id, 'location_id' => $placement->location_id]) }}"
           class="text-gray-500 text-sm hover:text-white flex items-center gap-1 mb-3 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke daftar siswa
        </a>
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-white">{{ $placement->student->name }}</h1>
                <p class="text-gray-400 text-sm mt-0.5">{{ $placement->location->name }} &middot; {{ $placement->period->name }}</p>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-2xl font-bold {{ $filled < $total ? 'text-red-400' : 'text-emerald-400' }}">{{ $filled }}/{{ $total }}</p>
                <p class="text-gray-500 text-xs">jurnal terisi</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Progress bar --}}
    @php $pct = $total > 0 ? round($filled / $total * 100) : 0; @endphp
    <div class="bg-gray-900 border border-white/5 rounded-xl p-4 mb-5">
        <div class="flex justify-between text-xs mb-2">
            <span class="text-gray-400">Progress jurnal</span>
            <span class="{{ $pct >= 80 ? 'text-emerald-400' : ($pct >= 50 ? 'text-amber-400' : 'text-red-400') }} font-semibold">{{ $pct }}%</span>
        </div>
        <div class="w-full bg-gray-800 rounded-full h-2">
            <div class="h-2 rounded-full {{ $pct >= 80 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                 style="width: {{ $pct }}%"></div>
        </div>
        <div class="flex justify-between text-xs mt-2 text-gray-600">
            <span class="text-emerald-400/70">{{ $filled }} hari terisi</span>
            <span class="text-red-400/70">{{ $total - $filled }} hari belum diisi</span>
        </div>
    </div>

    {{-- Daftar per hari --}}
    <div class="space-y-2">
        @foreach (array_reverse($days) as $key => $day)
            @php
                $journal    = $day['journal'];
                $absence    = $day['absence'];
                $attendance = $day['attendance'];
                $isPast     = $day['date']->lt(today());
                $isToday    = $day['date']->isToday();
            @endphp

            <div class="bg-gray-900 border {{ $journal ? 'border-amber-500/20' : ($isPast && !$absence ? 'border-red-500/15' : 'border-white/5') }} rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 gap-3">
                    <div class="flex items-center gap-3 flex-1">
                        <div class="w-1 h-8 rounded-full flex-shrink-0 {{ $journal ? 'bg-amber-500' : ($isPast && !$absence ? 'bg-red-500/60' : ($isToday ? 'bg-blue-500' : 'bg-gray-700')) }}"></div>
                        <div>
                            <p class="text-gray-200 text-sm font-medium">{{ $day['date']->translatedFormat('D, d M Y') }}</p>
                            <p class="text-xs mt-0.5 {{ $isToday ? 'text-blue-400' : 'text-gray-600' }}">
                                @if ($absence)
                                    <span class="{{ $absence->type === 'sakit' ? 'text-red-400' : ($absence->type === 'libur' ? 'text-blue-400' : 'text-orange-400') }}">
                                        {{ $absence->type_label }}
                                    </span>
                                @elseif ($attendance)
                                    <span class="text-emerald-400">Hadir {{ $attendance->selfie_taken_at?->format('H:i') ?? '' }}</span>
                                @elseif ($isPast)
                                    <span class="text-red-400">Tidak hadir</span>
                                @elseif ($isToday)
                                    Hari ini
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        @if ($journal)
                            <span class="px-2.5 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs rounded-lg">Terisi</span>
                        @elseif ($absence)
                            <span class="px-2.5 py-1 bg-gray-800 text-gray-500 text-xs rounded-lg">Tidak Wajib</span>
                        @elseif ($isPast)
                            <span class="px-2.5 py-1 bg-red-500/10 border border-red-500/20 text-red-400 text-xs rounded-lg">Tidak Diisi</span>
                        @elseif ($isToday)
                            <span class="px-2.5 py-1 bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs rounded-lg">Belum Diisi</span>
                        @else
                            <span class="text-gray-700 text-xs">—</span>
                        @endif
                    </div>
                </div>

                @if ($journal)
                    <div class="px-4 pb-4 border-t border-white/5">
                        {{-- Isi jurnal penuh --}}
                        <div x-data="{ expanded: false }" class="mt-3">
                            <p class="text-gray-300 text-sm leading-relaxed"
                               x-show="expanded || {{ strlen($journal->content) <= 300 ? 'true' : 'false' }}">
                                {{ $journal->content }}
                            </p>
                            @if (strlen($journal->content) > 300)
                                <p class="text-gray-300 text-sm leading-relaxed"
                                   x-show="!expanded">
                                    {{ Str::limit($journal->content, 300) }}
                                </p>
                                <button @click="expanded = !expanded"
                                        class="text-amber-400 text-xs mt-1 hover:text-amber-300 transition-colors">
                                    <span x-text="expanded ? 'Sembunyikan' : 'Baca selengkapnya'"></span>
                                </button>
                            @endif
                        </div>

                        {{-- Foto dokumentasi --}}
                        @if ($journal->photos->count() > 0)
                            <div class="mt-3">
                                <p class="text-gray-600 text-xs mb-2">{{ $journal->photos->count() }} foto dokumentasi</p>
                                <div class="flex gap-2 overflow-x-auto pb-1">
                                    @foreach ($journal->photos as $photo)
                                        <a href="{{ Storage::url($photo->photo_path) }}" target="_blank" class="flex-shrink-0">
                                            <img src="{{ Storage::url($photo->photo_path) }}" alt="{{ $photo->caption }}"
                                                 style="width:80px;height:80px;object-fit:cover"
                                                 class="rounded-xl border border-white/10 hover:border-amber-400/50 transition-colors"/>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Catatan guru --}}
                        @if ($journal->teacher_note)
                            <div class="mt-3 p-3 bg-blue-500/5 border border-blue-500/15 rounded-xl">
                                <p class="text-blue-400 text-xs font-semibold mb-1">Catatan Anda:</p>
                                <p class="text-blue-300 text-sm">{{ $journal->teacher_note }}</p>
                            </div>
                        @else
                            <form action="{{ route('guru.prakerin.journal.note', $journal) }}" method="POST" class="mt-3 flex gap-2">
                                @csrf
                                <input type="text" name="teacher_note" placeholder="Tulis catatan untuk jurnal ini..."
                                       class="flex-1 bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-blue-500/50 placeholder-gray-600 min-w-0">
                                <button type="submit" class="px-3 py-2 bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 text-xs rounded-xl border border-blue-500/20 transition-colors whitespace-nowrap">
                                    Simpan
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>

</x-simans-layout>
