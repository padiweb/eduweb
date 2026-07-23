<x-simans-layout title="Rekap Prakerin - {{ $placement->student->name }}">

    <div class="mb-6">
        <a href="{{ route('guru.prakerin.placements', ['period_id' => $placement->period_id]) }}"
           class="text-gray-500 text-sm hover:text-blue-600 flex items-center gap-1 mb-3 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg> Kembali
        </a>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $placement->student->name }}</h1>
                <p class="text-gray-500 text-sm mt-1">
                    {{ $placement->location->name }} · {{ $placement->period->name }}
                </p>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    @php
        $hadirCount = collect($days)->filter(fn($d) => $d['checkin'])->count();
        $jurnalCount = collect($days)->filter(fn($d) => $d['journal'])->count();
        $alfaCount = collect($days)->filter(fn($d) => ! $d['checkin'] && $d['date']->isPast())->count();
        $totalPast = collect($days)->filter(fn($d) => $d['date']->lte(today()))->count();
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $totalPast }}</p>
            <p class="text-gray-500 text-xs mt-0.5">Total Hari (s.d. hari ini)</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $hadirCount }}</p>
            <p class="text-gray-500 text-xs mt-0.5">Hadir Masuk</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-amber-600">{{ $jurnalCount }}</p>
            <p class="text-gray-500 text-xs mt-0.5">Jurnal Terisi</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $alfaCount }}</p>
            <p class="text-gray-500 text-xs mt-0.5">Tidak Hadir</p>
        </div>
    </div>

    {{-- Info --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div><p class="text-gray-500 text-xs">DU/DI</p><p class="text-gray-900 mt-0.5">{{ $placement->location->name }}</p></div>
            <div>
                <p class="text-gray-500 text-xs">Periode Efektif</p>
                <p class="text-gray-900 mt-0.5">
                    {{ $placement->getEffectiveStartDate()->format('d M Y') }} –
                    {{ $placement->getEffectiveEndDate()->format('d M Y') }}
                </p>
            </div>
            <div>
                <p class="text-gray-500 text-xs">Jam Masuk / Pulang</p>
                <p class="text-gray-900 mt-0.5">
                    {{ $placement->location->checkin_time ?? '—' }} / {{ $placement->location->checkout_time ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-gray-500 text-xs">Pembimbing Sekolah</p>
                @if ($placement->location->supervisors->count() > 0)
                    @foreach ($placement->location->supervisors as $sv)
                        <p class="text-gray-900 mt-0.5 text-xs">{{ $sv->name }}</p>
                    @endforeach
                @else
                    <p class="text-gray-500 mt-0.5">—</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabel rekap --}}
    <div class="tbl-card">
        <div class="px-5 py-3 border-b border-gray-200">
            <p class="text-sm font-semibold text-gray-900">Rekap Harian</p>
        </div>
        <div class="tbl-wrap">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">Tanggal</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">Masuk</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">Pulang</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">Jurnal</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">Foto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach (array_reverse($days) as $dateStr => $day)
                        @if ($day['date']->lte(today()))
                        <tr class="{{ $day['date']->isToday() ? 'bg-white/[0.02]' : 'hover:bg-white/[0.01]' }} transition-colors">
                            <td class="px-5 py-3">
                                <p class="text-gray-700 text-xs font-medium">
                                    {{ $day['date']->translatedFormat('D, d M Y') }}
                                </p>
                                @if ($day['date']->isToday())
                                    <span class="text-blue-600 text-xs">Hari ini</span>
                                @endif
                            </td>

                            <td class="px-5 py-3">
                                @if ($day['checkin'])
                                    <p class="text-blue-600 text-xs font-semibold">{{ $day['checkin']->selfie_taken_at?->format('H:i') ?? $day['checkin']->created_at->format('H:i') }}</p>
                                    <p class="text-gray-500 text-xs">{{ $day['checkin']->status_label }}</p>
                                    @if ($day['checkin']->selfie_path)
                                        <a href="{{ Storage::url($day['checkin']->selfie_path) }}" target="_blank">
                                            <img src="{{ Storage::url($day['checkin']->selfie_path) }}" alt="selfie masuk"
                                                 style="width:48px;height:48px;object-fit:cover"
                                                 class="rounded-lg mt-1 border border-gray-200 hover:border-emerald-200 transition-colors"/>
                                        </a>
                                    @endif
                                @else
                                    <span class="text-red-600 text-xs">Tidak absen</span>
                                @endif
                            </td>

                            <td class="px-5 py-3">
                                @if ($day['checkout'])
                                    <p class="text-blue-400 text-xs font-semibold">{{ $day['checkout']->selfie_taken_at?->format('H:i') ?? $day['checkout']->created_at->format('H:i') }}</p>
                                    @if ($day['checkout']->selfie_path)
                                        <a href="{{ Storage::url($day['checkout']->selfie_path) }}" target="_blank">
                                            <img src="{{ Storage::url($day['checkout']->selfie_path) }}" alt="selfie pulang"
                                                 style="width:48px;height:48px;object-fit:cover"
                                                 class="rounded-lg mt-1 border border-gray-200 hover:border-blue-200 transition-colors"/>
                                        </a>
                                    @endif
                                @else
                                    <span class="text-red-600 text-xs">Tidak absen</span>
                                @endif
                            </td>

                            <td class="px-5 py-3">
                                @if ($day['journal'])
                                    <div x-data="{ open: false }">
                                        <button @click="open = !open"
                                                class="text-amber-600 text-xs font-semibold hover:text-amber-700 flex items-center gap-1">
                                            Terisi
                                            <svg class="w-3 h-3 transition-transform" :class="open?'rotate-180':''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <p class="text-gray-500 text-xs">{{ $day['journal']->submitted_at?->format('H:i') ?? $day['journal']->updated_at->format('H:i') }}</p>
                                        <div x-show="open" x-cloak class="mt-2 p-3 bg-white rounded-xl max-w-sm">
                                            <p class="text-gray-600 text-xs leading-relaxed">{{ Str::limit($day['journal']->content, 250) }}</p>
                                            @if ($day['journal']->teacher_note)
                                                <div class="mt-2 pt-2 border-t border-gray-200">
                                                    <p class="text-blue-400 text-xs font-semibold">Catatan guru:</p>
                                                    <p class="text-blue-300 text-xs">{{ $day['journal']->teacher_note }}</p>
                                                </div>
                                            @else
                                                <form action="{{ route('guru.prakerin.journal.note', $day['journal']) }}"
                                                      method="POST" class="mt-2 pt-2 border-t border-gray-200">
                                                    @csrf
                                                    <textarea name="teacher_note" rows="2" placeholder="Tambah catatan..."
                                                              class="w-full bg-gray-100 border border-gray-200 text-gray-900 rounded-lg px-2 py-1.5 text-xs resize-none focus:outline-none focus:border-blue-400 placeholder-gray-400"></textarea>
                                                    <button type="submit"
                                                            class="mt-1 px-3 py-1 bg-blue-50 hover:bg-blue-600/40 text-blue-400 text-xs rounded-lg border border-blue-200 transition-colors">
                                                        Simpan Catatan
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-red-600 text-xs">Tidak diisi</span>
                                @endif
                            </td>

                            <td class="px-5 py-3">
                                @if ($day['journal'] && $day['journal']->photos->count() > 0)
                                    <div class="flex gap-1">
                                        @foreach ($day['journal']->photos->take(3) as $photo)
                                            <a href="{{ Storage::url($photo->photo_path) }}" target="_blank">
                                                <img src="{{ Storage::url($photo->photo_path) }}" alt="foto"
                                                     style="width:36px;height:36px;object-fit:cover"
                                                     class="rounded-lg border border-gray-200 hover:border-amber-200 transition-colors"/>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-900 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-simans-layout>
