<x-simans-layout title="Poin Pelanggaran">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Poin Pelanggaran</h1>
        <p class="text-gray-500 text-sm mt-1">{{ auth()->user()->name }}</p>
    </div>

    @php
        $student     = auth()->user();
        $school      = $student->school;
        $violations  = \App\Models\Violation::where('student_id', $student->id)
            ->where('is_archived', false)
            ->with(['category'])
            ->orderByDesc('incident_date')
            ->orderByDesc('created_at')
            ->get();
        $totalPoints = $violations->sum('points');

        // Level peringatan
        $w1 = $school->violation_warning1 ?? 10;
        $w2 = $school->violation_warning2 ?? 20;
        $w3 = $school->violation_warning3 ?? 30;

        $warningLevel = 0;
        if ($totalPoints >= $w3)      $warningLevel = 3;
        elseif ($totalPoints >= $w2)  $warningLevel = 2;
        elseif ($totalPoints >= $w1)  $warningLevel = 1;

        $sourceColors = [
            'manual'             => ['bg-red-50 border-red-200 text-red-600',       'Manual'],
            'absen_terlambat'    => ['bg-amber-50 border-amber-200 text-amber-600', 'Absen Terlambat'],
            'absen_alfa'         => ['bg-red-50 border-red-200 text-red-600',       'Alfa'],
            'tugas_terlambat'    => ['bg-amber-50 border-amber-200 text-amber-600', 'Tugas Terlambat'],
            'tugas_tidak_kumpul' => ['bg-orange-50 border-orange-200 text-orange-600', 'Tidak Kumpul'],
        ];
    @endphp

    {{-- Banner peringatan --}}
    @if($warningLevel === 3)
        <div class="mb-5 flex items-start gap-3 bg-red-900/30 border border-red-200 rounded-xl px-4 py-4">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
            <div>
                <p class="text-sm font-bold text-red-600">Peringatan 3</p>
                <p class="text-xs text-red-700 mt-0.5">Poin pelanggaranmu sudah mencapai {{ $totalPoints }} poin (batas: {{ $w3 }}). Segera temui kesiswaan.</p>
            </div>
        </div>
    @elseif($warningLevel === 2)
        <div class="mb-5 flex items-start gap-3 bg-orange-900/30 border border-orange-200 rounded-xl px-4 py-4">
            <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
            <div>
                <p class="text-sm font-bold text-orange-600">Peringatan 2</p>
                <p class="text-xs text-orange-700 mt-0.5">Poin pelanggaranmu sudah mencapai {{ $totalPoints }} poin (batas: {{ $w2 }}). Harap perhatikan kedisiplinan!</p>
            </div>
        </div>
    @elseif($warningLevel === 1)
        <div class="mb-5 flex items-start gap-3 bg-amber-900/30 border border-amber-200 rounded-xl px-4 py-4">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
            <div>
                <p class="text-sm font-bold text-amber-600">Peringatan 1</p>
                <p class="text-xs text-amber-700 mt-0.5">Poin pelanggaranmu sudah mencapai {{ $totalPoints }} poin (batas: {{ $w1 }}). Jaga kedisiplinanmu!</p>
            </div>
        </div>
    @endif

    {{-- Total poin + progress ke peringatan berikutnya --}}
    <div class="bg-white border {{ $warningLevel >= 3 ? 'border-red-200' : ($warningLevel >= 2 ? 'border-orange-200' : ($warningLevel >= 1 ? 'border-amber-200' : 'border-gray-200')) }} rounded-xl p-6 mb-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-4xl font-bold {{ $warningLevel >= 3 ? 'text-red-600' : ($warningLevel >= 2 ? 'text-orange-600' : ($warningLevel >= 1 ? 'text-amber-600' : 'text-gray-900')) }}">
                    {{ $totalPoints }}
                </p>
                <p class="text-gray-500 text-sm mt-0.5">Total Poin Pelanggaran</p>
            </div>
            @if($warningLevel > 0)
                <div class="text-right">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold
                        {{ $warningLevel >= 3 ? 'bg-red-50 text-red-600 border border-red-200' : ($warningLevel >= 2 ? 'bg-orange-50 text-orange-600 border border-orange-200' : 'bg-amber-50 text-amber-600 border border-amber-200') }}">
                        Peringatan {{ $warningLevel }}
                    </span>
                </div>
            @elseif($totalPoints === 0)
                <div class="w-12 h-12 rounded-full bg-blue-50 border border-blue-200 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            @endif
        </div>

        {{-- Progress bar ke peringatan berikutnya --}}
        @php
            $nextWarning = 0;
            $prevWarning = 0;
            if ($totalPoints < $w1) {
                $nextWarning = $w1; $prevWarning = 0;
            } elseif ($totalPoints < $w2) {
                $nextWarning = $w2; $prevWarning = $w1;
            } elseif ($totalPoints < $w3) {
                $nextWarning = $w3; $prevWarning = $w2;
            }
            $progress = $nextWarning > 0
                ? min(100, round((($totalPoints - $prevWarning) / ($nextWarning - $prevWarning)) * 100))
                : 100;
        @endphp

        @if($nextWarning > 0)
            <div class="mb-1 flex justify-between text-xs text-gray-500">
                <span>Menuju Peringatan {{ $warningLevel + 1 }}</span>
                <span>{{ $totalPoints }} / {{ $nextWarning }} poin</span>
            </div>
            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500
                    {{ $warningLevel >= 2 ? 'bg-red-500' : ($warningLevel >= 1 ? 'bg-orange-500' : 'bg-amber-500') }}"
                     style="width: {{ $progress }}%"></div>
            </div>
        @else
            <div class="h-2 bg-red-50 rounded-full overflow-hidden">
                <div class="h-full bg-red-500 rounded-full w-full"></div>
            </div>
            <p class="text-xs text-red-600 mt-1">Poin pelanggaran sudah melewati semua batas peringatan.</p>
        @endif

        {{-- Info batas peringatan --}}
        <div class="grid grid-cols-3 gap-2 mt-4">
            <div class="text-center bg-white rounded-xl p-2.5 border {{ $totalPoints >= $w1 ? 'border-amber-200' : 'border-gray-200' }}">
                <p class="text-xs font-semibold {{ $totalPoints >= $w1 ? 'text-amber-600' : 'text-gray-500' }}">P1</p>
                <p class="text-sm font-bold {{ $totalPoints >= $w1 ? 'text-amber-600' : 'text-gray-500' }}">{{ $w1 }}</p>
            </div>
            <div class="text-center bg-white rounded-xl p-2.5 border {{ $totalPoints >= $w2 ? 'border-orange-200' : 'border-gray-200' }}">
                <p class="text-xs font-semibold {{ $totalPoints >= $w2 ? 'text-orange-600' : 'text-gray-500' }}">P2</p>
                <p class="text-sm font-bold {{ $totalPoints >= $w2 ? 'text-orange-600' : 'text-gray-500' }}">{{ $w2 }}</p>
            </div>
            <div class="text-center bg-white rounded-xl p-2.5 border {{ $totalPoints >= $w3 ? 'border-red-200' : 'border-gray-200' }}">
                <p class="text-xs font-semibold {{ $totalPoints >= $w3 ? 'text-red-600' : 'text-gray-500' }}">P3</p>
                <p class="text-sm font-bold {{ $totalPoints >= $w3 ? 'text-red-600' : 'text-gray-500' }}">{{ $w3 }}</p>
            </div>
        </div>
    </div>

    {{-- Breakdown per sumber --}}
    @if($violations->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
            @php
                $terlambat = $violations->where('source', 'absen_terlambat')->sum('points');
                $alfa      = $violations->where('source', 'absen_alfa')->sum('points');
                $tugas     = $violations->whereIn('source', ['tugas_terlambat','tugas_tidak_kumpul'])->sum('points');
                $manual    = $violations->where('source', 'manual')->sum('points');
            @endphp
            <div class="bg-white border border-amber-200 rounded-xl p-3 text-center">
                <p class="text-xl font-bold text-amber-600">{{ $terlambat }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Terlambat</p>
            </div>
            <div class="bg-white border border-red-200 rounded-xl p-3 text-center">
                <p class="text-xl font-bold text-red-600">{{ $alfa }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Alfa</p>
            </div>
            <div class="bg-white border border-orange-200 rounded-xl p-3 text-center">
                <p class="text-xl font-bold text-orange-600">{{ $tugas }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Tugas</p>
            </div>
            <div class="bg-white border border-red-200 rounded-xl p-3 text-center">
                <p class="text-xl font-bold text-red-600">{{ $manual }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Tata Tertib</p>
            </div>
        </div>
    @endif

    {{-- Daftar pelanggaran --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-900">Riwayat Pelanggaran</h2>
        </div>

        @if($violations->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($violations as $v)
                    @php [$badgeClass, $sourceLabel] = $sourceColors[$v->source] ?? ['bg-white text-gray-500 border-gray-200', $v->source]; @endphp
                    <div class="flex items-start gap-4 px-5 py-4">
                        <div class="w-10 h-10 rounded-full bg-red-50 border border-red-200 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="text-sm font-bold text-red-600">{{ $v->points }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                <p class="text-sm font-medium text-gray-900">{{ $v->category->name }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $badgeClass }}">
                                    {{ $sourceLabel }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">{{ $v->description }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $v->incident_date->translatedFormat('l, d F Y') }}
                                @if(! $v->isAutomatic()) · {{ $v->reportedBy->name }} @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <svg class="w-12 h-12 text-blue-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
                <p class="text-gray-500 text-sm">Kamu belum punya pelanggaran.</p>
                <p class="text-gray-500 text-xs mt-1">Pertahankan kedisiplinan!</p>
            </div>
        @endif
    </div>

    {{-- Info batas alfa per semester --}}
    @php $alfaLimit = $school->alfa_limit_per_semester ?? 0; @endphp
    @if($alfaLimit > 0)
        @php
            // Hitung alfa semester ini
            $currentAcademicYear = $student->classrooms()
                ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                ->with('academicYear')
                ->first()?->academicYear;

            $alfaThisSemester = 0;
            if ($currentAcademicYear) {
                $alfaThisSemester = \App\Models\Attendance::where('student_id', $student->id)
                    ->where('status', 'alfa')
                    ->whereBetween('scanned_at', [
                        $currentAcademicYear->start_date,
                        $currentAcademicYear->end_date ?? now(),
                    ])
                    ->count();
            }
            $alfaColor = $alfaThisSemester >= $alfaLimit ? 'red' : ($alfaThisSemester >= ($alfaLimit * 0.7) ? 'amber' : 'gray');
        @endphp
        <div class="mt-4 bg-white border border-{{ $alfaColor === 'red' ? 'red' : ($alfaColor === 'amber' ? 'amber' : 'white') }}-500/{{ $alfaColor === 'gray' ? '5' : '20' }} rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-gray-500">Alfa Semester Ini</p>
                <span class="text-sm font-bold text-{{ $alfaColor === 'red' ? 'red' : ($alfaColor === 'amber' ? 'amber' : 'white') }}-400">
                    {{ $alfaThisSemester }} / {{ $alfaLimit }} hari
                </span>
            </div>
            <div class="h-1.5 bg-white rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 bg-{{ $alfaColor === 'red' ? 'red' : ($alfaColor === 'amber' ? 'amber' : 'emerald') }}-500"
                     style="width: {{ min(100, round(($alfaThisSemester / $alfaLimit) * 100)) }}%"></div>
            </div>
            @if($alfaThisSemester >= $alfaLimit)
                <p class="text-xs text-red-600 mt-1.5">Kamu sudah melewati batas alfa semester ini. Segera hubungi kesiswaan.</p>
            @elseif($alfaColor === 'amber')
                <p class="text-xs text-amber-600 mt-1.5">Kamu mendekati batas alfa semester ini. Jaga kehadiranmu!</p>
            @endif
        </div>
    @endif

    <div class="mt-4 bg-white border border-gray-200 rounded-xl px-4 py-3">
        <p class="text-xs text-gray-500 leading-relaxed">
            Poin 1–3 otomatis dari sistem (terlambat, alfa, tugas). Poin tata tertib dicatat oleh kesiswaan.
            Poin berlaku selama kamu menjadi siswa aktif dan tidak hilang saat ganti semester.
            Jika ada kekeliruan, hubungi guru atau kesiswaan.
        </p>
    </div>

</x-simans-layout>