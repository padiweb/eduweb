<x-simans-layout title="Tugas Saya">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tugas Saya</h1>
        <p class="text-gray-500 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div class="flex gap-3 mb-5">
        <a href="{{ route('siswa.assignments.index') }}"
           class="text-sm font-semibold text-gray-900 bg-white border border-gray-200 px-4 py-2 rounded-xl">
            Tugas
        </a>
        <a href="{{ route('siswa.assignments.scores') }}"
           class="text-sm font-medium text-gray-500 hover:text-gray-900 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-colors">
            Nilai Saya
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tugas aktif --}}
    @if($activeAssignments->count() > 0)
        <h2 class="text-sm font-semibold text-gray-900 mb-3">Tugas Aktif ({{ $activeAssignments->count() }})</h2>
        <div class="space-y-3 mb-6">
            @foreach($activeAssignments as $a)
                @php
                    $sub       = $a->submissions->first();
                    $submitted = $sub !== null && ! $sub->isNotSubmitted();
                    $isPast    = $a->isPastDeadline();
                @endphp
                <div class="bg-white border {{ $submitted ? 'border-blue-200' : ($isPast ? 'border-amber-500/20' : 'border-gray-200') }} rounded-xl p-4">
                    {{-- Info tugas --}}
                    <div class="mb-3">
                        <p class="text-sm font-semibold text-gray-900">{{ $a->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $a->subject->name }} &middot; {{ $a->teacher->name }}
                            @if($a->deadline)
                                &middot; <span class="{{ $isPast ? 'text-amber-400' : 'text-gray-500' }}">{{ $a->deadline->translatedFormat('d M Y H:i') }}</span>
                            @endif
                        </p>
                        @if($a->description)
                            <p class="text-xs text-gray-500 mt-1.5 line-clamp-2">{{ $a->description }}</p>
                        @endif
                    </div>

                    {{-- Aksi di baris bawah --}}
                    <div class="flex items-center justify-between gap-2">
                        @if($submitted)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-600/10 text-blue-600 border border-blue-200 flex-shrink-0">
                                {{ $sub->score !== null ? 'Nilai: '.$sub->score : 'Sudah Kumpul' }}
                            </span>
                        @else
                            <span class="text-xs text-gray-500">Belum dikumpulkan</span>
                        @endif
                        <a href="{{ route('siswa.assignments.show', $a->id) }}"
                           class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold {{ $submitted ? 'bg-white hover:bg-gray-50 text-gray-400 border border-gray-200' : 'bg-blue-600 hover:bg-blue-700 text-white' }} transition-colors">
                            {{ $submitted ? 'Lihat / Revisi' : 'Kumpulkan' }}
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Tugas ditutup --}}
    @if($closedAssignments->count() > 0)
        <h2 class="text-sm font-semibold text-gray-500 mb-3">Tugas Selesai ({{ $closedAssignments->count() }})</h2>
        <div class="space-y-2">
            @foreach($closedAssignments as $a)
                @php $sub = $a->submissions->first(); @endphp
                <a href="{{ route('siswa.assignments.show', $a->id) }}"
                   class="block bg-white border {{ $sub?->isNotSubmitted() ? 'border-red-500/20' : 'border-gray-200' }} rounded-xl p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-500 truncate">{{ $a->title }}</p>
                            <p class="text-xs text-gray-500">{{ $a->subject->name }}</p>
                        </div>
                        @if($sub?->isNotSubmitted())
                            <span class="text-xs font-semibold text-red-400 flex-shrink-0 bg-red-500/10 border border-red-500/20 px-2.5 py-1 rounded-full">
                                Tidak Dikumpulkan
                            </span>
                        @elseif($sub && $sub->score !== null)
                            <span class="text-lg font-bold flex-shrink-0 {{ $sub->score >= 80 ? 'text-blue-600' : ($sub->score >= 60 ? 'text-amber-400' : 'text-red-400') }}">
                                {{ $sub->score }}
                            </span>
                        @elseif($sub)
                            <span class="text-xs text-gray-500 flex-shrink-0">Belum dinilai</span>
                        @else
                            <span class="text-xs text-gray-500 flex-shrink-0">-</span>
                        @endif
                        <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    @if($activeAssignments->count() === 0 && $closedAssignments->count() === 0)
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-gray-900 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            <p class="text-gray-500 text-sm">Belum ada tugas dari guru.</p>
        </div>
    @endif

</x-simans-layout>