<x-simans-layout title="Riwayat Absensi">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Riwayat Absensi</h1>
        <p class="text-gray-500 text-sm mt-1">{{ auth()->user()->name }} · NIS {{ auth()->user()->nis }}</p>
    </div>

    {{-- Filter bulan / tahun --}}
    <form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
        <select name="bulan"
                class="bg-white border border-gray-200 text-gray-900 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition-colors">
            @foreach($months as $m)
                <option value="{{ $m['value'] }}" {{ $month == $m['value'] ? 'selected' : '' }}>
                    {{ $m['label'] }}
                </option>
            @endforeach
        </select>
        <select name="tahun"
                class="bg-white border border-gray-200 text-gray-900 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition-colors">
            @for($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            Tampilkan
        </button>

        @if($academicYears->count() > 0)
            <div class="ml-auto flex items-center gap-2">
                <select name="semester_id"
                        class="bg-white border border-gray-200 text-gray-900 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    <option value="">Rekap Semester...</option>
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ request('semester_id') == $ay->id ? 'selected' : '' }}>
                            {{ $ay->label }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </form>

    {{-- Rekap semester --}}
    @if($semesterRecap)
        <div class="bg-white border border-blue-200 rounded-xl p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Rekap Semester</h2>
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                @foreach([
                    ['label' => 'Hadir',     'value' => $semesterRecap['hadir'],                        'color' => 'emerald'],
                    ['label' => 'Terlambat', 'value' => $semesterRecap['terlambat'],                    'color' => 'amber'],
                    ['label' => 'Izin',      'value' => $semesterRecap['izin'],                         'color' => 'blue'],
                    ['label' => 'Sakit',     'value' => $semesterRecap['sakit'],                        'color' => 'blue'],
                    ['label' => 'Alfa',      'value' => $semesterRecap['alfa'],                         'color' => 'red'],
                    ['label' => 'Kehadiran', 'value' => $semesterRecap['attendance_rate'] . '%',        'color' => 'emerald'],
                ] as $s)
                    <div class="bg-white rounded-xl p-3 text-center">
                        <p class="text-xl font-bold text-{{ $s['color'] }}-400">{{ $s['value'] }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $s['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Rekap bulan --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @foreach([
            ['label' => 'Hadir',     'value' => $recap['hadir'],     'color' => 'emerald'],
            ['label' => 'Terlambat', 'value' => $recap['terlambat'], 'color' => 'amber'],
            ['label' => 'Izin',      'value' => $recap['izin'],      'color' => 'blue'],
            ['label' => 'Sakit',     'value' => $recap['sakit'],     'color' => 'blue'],
            ['label' => 'Alfa',      'value' => $recap['alfa'],      'color' => 'red'],
        ] as $s)
            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-{{ $s['color'] }}-400">{{ $s['value'] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $s['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Daftar per hari — terbaru di atas --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">
                Detail Absensi —
                {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}
            </h2>
            <span class="text-xs text-gray-500">{{ $recap['records']->count() }} hari</span>
        </div>

        @if($recap['records']->count() > 0)
            <div class="divide-y divide-gray-100">
                {{-- sortByDesc session_date agar terbaru di atas --}}
                @foreach($recap['records']->sortByDesc(fn($att) => $att->session->session_date) as $att)
                    @php
                        $colorMap = [
                            'hadir'     => 'text-blue-600 bg-blue-50 border-blue-200',
                            'terlambat' => 'text-amber-600 bg-amber-50 border-amber-200',
                            'izin'      => 'text-blue-400 bg-blue-50 border-blue-200',
                            'sakit'     => 'text-blue-600 bg-blue-50 border-blue-200',
                            'alfa'      => 'text-red-600 bg-red-50 border-red-200',
                        ];
                        $labelMap = ['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alfa'=>'Alfa'];
                        $isToday  = $att->session->session_date->isToday();
                    @endphp
                    <div class="flex items-center gap-4 px-5 py-3.5 {{ $isToday ? 'bg-blue-50' : '' }}">

                        {{-- Tanggal --}}
                        <div class="text-center w-12 flex-shrink-0">
                            <p class="text-lg font-bold {{ $isToday ? 'text-blue-600' : 'text-gray-900' }}">
                                {{ $att->session->session_date->format('d') }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $att->session->session_date->translatedFormat('D') }}
                            </p>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 flex items-center gap-2">
                                {{ $att->session->session_date->translatedFormat('l, d F Y') }}
                                @if($isToday)
                                    <span class="text-xs text-blue-600 font-medium">Hari ini</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if($att->scanned_at)
                                    Scan: {{ $att->scanned_at->format('H:i:s') }}
                                @else
                                    Input manual
                                @endif
                                @if($att->is_manual_entry)
                                    · <span class="text-amber-500">Manual oleh guru</span>
                                @endif
                            </p>
                        </div>

                        {{-- Status badge --}}
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border flex-shrink-0 {{ $colorMap[$att->status] ?? 'text-gray-500 bg-white border-gray-200' }}">
                            {{ $labelMap[$att->status] ?? $att->status }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <svg class="w-12 h-12 text-blue-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
                <p class="text-gray-500 text-sm">Tidak ada data absensi untuk bulan ini.</p>
            </div>
        @endif
    </div>

</x-simans-layout>