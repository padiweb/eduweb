<x-simans-layout title="Tugas Saya">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Tugas Saya</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div class="flex gap-3 mb-5">
        <a href="{{ route('siswa.assignments.index') }}"
           class="text-sm font-semibold text-white bg-gray-800 border border-white/10 px-4 py-2 rounded-xl">
            Tugas
        </a>
        <a href="{{ route('siswa.assignments.scores') }}"
           class="text-sm font-medium text-gray-400 hover:text-white bg-gray-900 border border-white/5 px-4 py-2 rounded-xl transition-colors">
            Nilai Saya
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-300 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tugas aktif --}}
    @if($activeAssignments->count() > 0)
        <h2 class="text-sm font-semibold text-white mb-3">Tugas Aktif ({{ $activeAssignments->count() }})</h2>
        <div class="space-y-3 mb-6">
            @foreach($activeAssignments as $a)
                @php
                    $sub       = $a->submissions->first();
                    $submitted = $sub !== null;
                    $isPast    = $a->isPastDeadline();
                @endphp
                <div class="bg-gray-900 border {{ $submitted ? 'border-emerald-500/20' : ($isPast ? 'border-amber-500/20' : 'border-white/5') }} rounded-xl p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white">{{ $a->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $a->subject->name }} · {{ $a->teacher->name }}
                                @if($a->deadline)
                                    · Deadline: <span class="{{ $isPast ? 'text-amber-400' : 'text-gray-400' }}">{{ $a->deadline->translatedFormat('d M Y H:i') }}</span>
                                @endif
                            </p>
                            @if($a->description)
                                <p class="text-xs text-gray-400 mt-1.5 line-clamp-2">{{ $a->description }}</p>
                            @endif
                        </div>
                        <div class="flex-shrink-0">
                            @if($submitted)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    {{ $sub->score !== null ? 'Nilai: '.$sub->score : 'Sudah Kumpul' }}
                                </span>
                            @else
                                <a href="{{ route('siswa.assignments.show', $a->id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-500 hover:bg-emerald-600 text-white transition-colors">
                                    Kumpulkan
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Tugas ditutup --}}
    @if($closedAssignments->count() > 0)
        <h2 class="text-sm font-semibold text-gray-400 mb-3">Tugas Selesai ({{ $closedAssignments->count() }})</h2>
        <div class="space-y-2">
            @foreach($closedAssignments as $a)
                @php $sub = $a->submissions->first(); @endphp
                <div class="bg-gray-900 border border-white/5 rounded-xl p-4 flex items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-400 truncate">{{ $a->title }}</p>
                        <p class="text-xs text-gray-600">{{ $a->subject->name }}</p>
                    </div>
                    @if($sub && $sub->score !== null)
                        <span class="text-lg font-bold {{ $sub->score >= 80 ? 'text-emerald-400' : ($sub->score >= 60 ? 'text-amber-400' : 'text-red-400') }} flex-shrink-0">
                            {{ $sub->score }}
                        </span>
                    @elseif($sub)
                        <span class="text-xs text-gray-500 flex-shrink-0">Belum dinilai</span>
                    @else
                        <span class="text-xs text-red-400 flex-shrink-0">Tidak kumpul</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if($activeAssignments->count() === 0 && $closedAssignments->count() === 0)
        <div class="bg-gray-900 border border-white/5 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            <p class="text-gray-500 text-sm">Belum ada tugas dari guru.</p>
        </div>
    @endif

</x-simans-layout>
