<x-simans-layout title="Nilai Saya">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Nilai Saya</h1>
        <p class="text-gray-500 text-sm mt-1">Rata-rata nilai tugas per mata pelajaran</p>
    </div>

    <div class="tab-nav-scroll">
        <a href="{{ route('siswa.assignments.index') }}"
           class="text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-colors">
            Tugas
        </a>
        <a href="{{ route('siswa.assignments.scores') }}"
           class="text-sm font-semibold text-blue-600 bg-blue-50 border border-blue-200 px-4 py-2 rounded-xl">
            Nilai Saya
        </a>
    </div>

    @if($subjectScores->count() > 0)
        <div class="space-y-4">
            @foreach($subjectScores as $row)
                <div class="tbl-card">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ $row['subject']->name }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $row['graded_count'] }} dari {{ $row['total'] }} tugas sudah dinilai</p>
                        </div>
                        @if($row['average'] !== null)
                            <div class="text-center">
                                <p class="text-2xl font-bold {{ $row['average'] >= 80 ? 'text-blue-600' : ($row['average'] >= 70 ? 'text-blue-600' : ($row['average'] >= 60 ? 'text-amber-600' : 'text-red-600')) }}">
                                    {{ $row['average'] }}
                                </p>
                                <p class="text-xs text-gray-500">rata-rata</p>
                            </div>
                        @else
                            <span class="text-xs text-gray-500">Belum ada nilai</span>
                        @endif
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($row['assignments'] as $a)
                            @php $sub = $a->submissions->first(); @endphp
                            <div class="flex items-center gap-3 px-5 py-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm text-gray-900 truncate">{{ $a->title }}</p>
                                        @if(! $a->is_closed)
                                            <span class="text-xs text-blue-600 bg-blue-50 border border-blue-200 px-1.5 py-0.5 rounded-full flex-shrink-0">Aktif</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        @if($sub && $sub->submitted_at)
                                            Dikumpulkan {{ \Carbon\Carbon::parse($sub->submitted_at)->translatedFormat('d M Y') }}
                                            @if($sub->isLate()) &middot; <span class="text-amber-600">Terlambat</span> @endif
                                        @elseif($sub && $sub->isNotSubmitted())
                                            <span class="text-red-600">Tidak dikumpulkan</span>
                                        @else
                                            Belum dikumpulkan
                                        @endif
                                    </p>
                                    {{-- Komentar guru --}}
                                    @if($sub?->feedback)
                                        <p class="text-xs text-blue-600 mt-0.5 italic">"{{ $sub->feedback }}"</p>
                                    @endif
                                </div>
                                @if($sub && $sub->score !== null)
                                    <span class="text-base font-bold flex-shrink-0 {{ $sub->score >= 80 ? 'text-blue-600' : ($sub->score >= 60 ? 'text-amber-600' : 'text-red-600') }}">
                                        {{ $sub->score }}
                                    </span>
                                @elseif($sub && ! $sub->isNotSubmitted())
                                    <span class="text-xs text-gray-500 flex-shrink-0">Belum dinilai</span>
                                @elseif($sub && $sub->isNotSubmitted())
                                    <span class="text-xs text-red-600 flex-shrink-0">Tidak kumpul</span>
                                @else
                                    <span class="text-xs text-gray-500 flex-shrink-0">-</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-blue-600 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
            </svg>
            <p class="text-gray-500 text-sm">Belum ada nilai. Kumpulkan tugas terlebih dahulu.</p>
        </div>
    @endif

</x-simans-layout>