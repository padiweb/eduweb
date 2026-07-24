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
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f1f5f9">
                        <div>
                            <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0">{{ $row['subject']->name }}</h3>
                            <p style="font-size:11.5px;color:#94a3b8;margin:3px 0 0">{{ $row['graded_count'] }} dari {{ $row['total'] }} tugas sudah dinilai</p>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px">
                            @if($row['average'] !== null)
                                @php
                                    $avgColor = $row['average'] >= 80 ? '#059669' : ($row['average'] >= 70 ? '#2563eb' : ($row['average'] >= 60 ? '#d97706' : '#dc2626'));
                                    $avgBg    = $row['average'] >= 80 ? '#ecfdf5' : ($row['average'] >= 70 ? '#eff6ff' : ($row['average'] >= 60 ? '#fffbeb' : '#fff1f2'));
                                @endphp
                                {{-- Trend arrow --}}
                                @if(isset($row['trend']))
                                    @if($row['trend'] === 'up')
                                        <div style="display:flex;align-items:center;gap:3px;background:#ecfdf5;border:1px solid #bbf7d0;padding:4px 8px;border-radius:20px">
                                            <svg width="12" height="12" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                                            <span style="font-size:11px;font-weight:700;color:#059669">Naik</span>
                                        </div>
                                    @elseif($row['trend'] === 'down')
                                        <div style="display:flex;align-items:center;gap:3px;background:#fff1f2;border:1px solid #fecaca;padding:4px 8px;border-radius:20px">
                                            <svg width="12" height="12" fill="none" stroke="#dc2626" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                                            <span style="font-size:11px;font-weight:700;color:#dc2626">Turun</span>
                                        </div>
                                    @else
                                        <div style="display:flex;align-items:center;gap:3px;background:#f8fafc;border:1px solid #e2e8f0;padding:4px 8px;border-radius:20px">
                                            <svg width="12" height="12" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                                            <span style="font-size:11px;color:#64748b">Stabil</span>
                                        </div>
                                    @endif
                                @endif
                                <div style="width:50px;height:50px;border-radius:12px;background:{{ $avgBg }};display:flex;flex-direction:column;align-items:center;justify-content:center">
                                    <span style="font-size:20px;font-weight:800;color:{{ $avgColor }};line-height:1">{{ $row['average'] }}</span>
                                    <span style="font-size:10px;color:#94a3b8">rata-rata</span>
                                </div>
                            @else
                                <span style="font-size:12px;color:#94a3b8">Belum ada nilai</span>
                            @endif
                        </div>
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