<x-simans-layout title="Poin Pelanggaran">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Poin Pelanggaran</h1>
        <p class="text-gray-400 text-sm mt-1">{{ auth()->user()->name }}</p>
    </div>

    @php
        $student     = auth()->user();
        $violations  = \App\Models\Violation::where('student_id', $student->id)
            ->where('is_archived', false)
            ->with(['category'])
            ->orderByDesc('incident_date')
            ->orderByDesc('created_at')
            ->get();
        $totalPoints = $violations->sum('points');

        $sourceColors = [
            'manual'             => ['bg-red-500/10 border-red-500/20 text-red-400',    'Manual'],
            'absen_terlambat'    => ['bg-amber-500/10 border-amber-500/20 text-amber-400', 'Absen Terlambat'],
            'absen_alfa'         => ['bg-red-500/10 border-red-500/20 text-red-400',    'Alfa'],
            'tugas_terlambat'    => ['bg-amber-500/10 border-amber-500/20 text-amber-400', 'Tugas Terlambat'],
            'tugas_tidak_kumpul' => ['bg-orange-500/10 border-orange-500/20 text-orange-400', 'Tidak Kumpul'],
        ];
    @endphp

    {{-- Total poin --}}
    <div class="bg-gray-900 border {{ $totalPoints >= 20 ? 'border-red-500/30' : ($totalPoints >= 10 ? 'border-amber-500/30' : 'border-white/5') }} rounded-xl p-6 mb-6 text-center">
        <p class="text-5xl font-bold {{ $totalPoints >= 20 ? 'text-red-400' : ($totalPoints >= 10 ? 'text-amber-400' : 'text-white') }} mb-1">
            {{ $totalPoints }}
        </p>
        <p class="text-gray-400 text-sm">Total Poin Pelanggaran</p>

        @if($totalPoints >= 20)
            <div class="mt-3 bg-red-900/20 border border-red-500/20 rounded-xl px-4 py-2 text-xs text-red-300">
                Poin pelanggaran kamu sudah tinggi. Harap lebih tertib!
            </div>
        @elseif($totalPoints >= 10)
            <div class="mt-3 bg-amber-900/20 border border-amber-500/20 rounded-xl px-4 py-2 text-xs text-amber-300">
                Poin pelanggaran kamu sudah cukup banyak. Perhatikan kedisiplinan!
            </div>
        @elseif($totalPoints == 0)
            <div class="mt-3 bg-emerald-900/20 border border-emerald-500/20 rounded-xl px-4 py-2 text-xs text-emerald-300">
                Kamu belum punya poin pelanggaran. Pertahankan!
            </div>
        @endif
    </div>

    {{-- Breakdown per sumber --}}
    @if($violations->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            @php
                $terlambat = $violations->where('source', 'absen_terlambat')->sum('points');
                $alfa      = $violations->where('source', 'absen_alfa')->sum('points');
                $tugas     = $violations->whereIn('source', ['tugas_terlambat','tugas_tidak_kumpul'])->sum('points');
                $manual    = $violations->where('source', 'manual')->sum('points');
            @endphp
            <div class="bg-gray-900 border border-amber-500/20 rounded-xl p-4 text-center">
                <p class="text-xl font-bold text-amber-400">{{ $terlambat }}</p>
                <p class="text-xs text-gray-500 mt-1">Terlambat</p>
            </div>
            <div class="bg-gray-900 border border-red-500/20 rounded-xl p-4 text-center">
                <p class="text-xl font-bold text-red-400">{{ $alfa }}</p>
                <p class="text-xs text-gray-500 mt-1">Alfa</p>
            </div>
            <div class="bg-gray-900 border border-orange-500/20 rounded-xl p-4 text-center">
                <p class="text-xl font-bold text-orange-400">{{ $tugas }}</p>
                <p class="text-xs text-gray-500 mt-1">Tugas</p>
            </div>
            <div class="bg-gray-900 border border-red-500/20 rounded-xl p-4 text-center">
                <p class="text-xl font-bold text-red-400">{{ $manual }}</p>
                <p class="text-xs text-gray-500 mt-1">Tata Tertib</p>
            </div>
        </div>
    @endif

    {{-- Daftar pelanggaran --}}
    <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5">
            <h2 class="text-sm font-semibold text-white">Riwayat Pelanggaran</h2>
        </div>

        @if($violations->count() > 0)
            <div class="divide-y divide-white/5">
                @foreach($violations as $v)
                    @php
                        [$badgeClass, $sourceLabel] = $sourceColors[$v->source] ?? ['bg-gray-800 text-gray-400 border-white/10', $v->source];
                    @endphp
                    <div class="flex items-start gap-4 px-5 py-4">
                        {{-- Poin --}}
                        <div class="w-10 h-10 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="text-sm font-bold text-red-400">{{ $v->points }}</span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <p class="text-sm font-medium text-white">{{ $v->category->name }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $badgeClass }}">
                                    {{ $sourceLabel }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400">{{ $v->description }}</p>
                            <p class="text-xs text-gray-600 mt-1">
                                {{ $v->incident_date->translatedFormat('l, d F Y') }}
                                @if(! $v->isAutomatic())
                                    · Dicatat oleh {{ $v->reportedBy->name }}
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
                <p class="text-gray-500 text-sm">Kamu belum punya pelanggaran.</p>
                <p class="text-gray-600 text-xs mt-1">Pertahankan kedisiplinan!</p>
            </div>
        @endif
    </div>

    {{-- Info catatan --}}
    <div class="mt-4 bg-gray-900 border border-white/5 rounded-xl px-4 py-3">
        <p class="text-xs text-gray-500 leading-relaxed">
            Poin 1–3 otomatis dari sistem (terlambat, alfa, tugas).
            Poin tata tertib dicatat oleh kesiswaan.
            Poin berlaku selama kamu menjadi siswa aktif.
            Jika ada kekeliruan, hubungi guru atau kesiswaan.
        </p>
    </div>

</x-simans-layout>
