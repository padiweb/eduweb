<x-simans-layout title="Dashboard">

@php
    $statusBg    = match($todayAttendance?->status) { 'hadir'=>'linear-gradient(135deg,#10b981,#059669)', 'terlambat'=>'linear-gradient(135deg,#f59e0b,#d97706)', 'alfa'=>'linear-gradient(135deg,#ef4444,#dc2626)', default=>'linear-gradient(135deg,#6b7280,#4b5563)' };
    $statusText  = match($todayAttendance?->status) { 'hadir'=>'Hadir Tepat Waktu', 'terlambat'=>'Hadir Terlambat', 'alfa'=>'Tidak Hadir', default=>'Belum Absen Hari Ini' };
    $statusSub   = $todayAttendance?->scanned_at ? 'Scan pukul '.$todayAttendance->scanned_at->format('H:i').' WIB' : 'Scan QR sebelum '.substr(auth()->user()->school->attendance_close_time ?? '07:30:00',0,5);
@endphp

{{-- Header greeting --}}
<div style="margin-bottom:20px">
    <h1 style="font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;margin:0 0 4px">Halo, {{ auth()->user()->name }}!</h1>
    <p style="font-size:13px;color:#64748b;margin:0">NIS: {{ auth()->user()->nis }} &middot; {{ now()->translatedFormat('l, d F Y') }}</p>
</div>

{{-- Status banner --}}
<div style="background:{{ $statusBg }};border-radius:16px;padding:18px 20px;margin-bottom:16px;display:flex;align-items:center;gap:16px;box-shadow:0 4px 16px rgba(15,23,42,0.12)">
    <div style="width:48px;height:48px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        @if($todayAttendance?->status === 'hadir')
            <svg width="22" height="22" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @elseif($todayAttendance?->status === 'terlambat')
            <svg width="22" height="22" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @elseif($todayAttendance?->status === 'alfa')
            <svg width="22" height="22" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        @else
            <svg width="22" height="22" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5z"/></svg>
        @endif
    </div>
    <div style="flex:1">
        <p style="font-size:16px;font-weight:700;color:white;margin:0">{{ $statusText }}</p>
        <p style="font-size:12px;color:rgba(255,255,255,.75);margin:4px 0 0">{{ $statusSub }}</p>
    </div>
    @if(!$todayAttendance)
    <a href="{{ route('siswa.attendance.absensi') }}" style="font-size:12px;font-weight:700;color:white;background:rgba(255,255,255,0.2);padding:8px 16px;border-radius:8px;text-decoration:none;white-space:nowrap">Absen Sekarang</a>
    @endif
</div>

{{-- Stats bulan ini --}}
<div id="stat-row-siswa" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px;text-align:center;box-shadow:0 1px 3px rgba(15,23,42,0.05)">
        <div style="font-size:24px;font-weight:800;color:#1e40af;letter-spacing:-1px">{{ $rate }}%</div>
        <div style="font-size:11px;font-weight:600;color:#64748b;margin-top:4px">Kehadiran</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px;text-align:center;box-shadow:0 1px 3px rgba(15,23,42,0.05)">
        <div style="font-size:24px;font-weight:800;color:#059669;letter-spacing:-1px">{{ $monthStats['hadir'] ?? 0 }}</div>
        <div style="font-size:11px;font-weight:600;color:#64748b;margin-top:4px">Hadir</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px;text-align:center;box-shadow:0 1px 3px rgba(15,23,42,0.05)">
        <div style="font-size:24px;font-weight:800;color:#d97706;letter-spacing:-1px">{{ $monthStats['terlambat'] ?? 0 }}</div>
        <div style="font-size:11px;font-weight:600;color:#64748b;margin-top:4px">Terlambat</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px;text-align:center;box-shadow:0 1px 3px rgba(15,23,42,0.05)">
        <div style="font-size:24px;font-weight:800;color:#dc2626;letter-spacing:-1px">{{ $monthStats['alfa'] ?? 0 }}</div>
        <div style="font-size:11px;font-weight:600;color:#64748b;margin-top:4px">Alfa</div>
    </div>
</div>

{{-- Alert pelanggaran --}}
@if($violationPoints > 0)
<div style="background:#fff1f2;border:1px solid #fecaca;border-radius:12px;padding:14px 16px;margin-bottom:12px;display:flex;align-items:center;gap:12px">
    <div style="width:36px;height:36px;background:#fee2e2;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <svg width="16" height="16" fill="none" stroke="#dc2626" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
    </div>
    <div style="flex:1">
        <p style="font-size:13.5px;font-weight:700;color:#dc2626;margin:0">Poin Pelanggaran: {{ $violationPoints }}</p>
        <a href="{{ route('siswa.violations') }}" style="font-size:11px;color:#dc2626;opacity:.7;text-decoration:none">Lihat detail →</a>
    </div>
</div>
@endif

{{-- Alert tagihan --}}
@if($activeBills > 0)
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:14px 16px;margin-bottom:12px;display:flex;align-items:center;gap:12px">
    <div style="width:36px;height:36px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
    </div>
    <div style="flex:1">
        <p style="font-size:13.5px;font-weight:700;color:#b45309;margin:0">{{ $activeBills }} Tagihan Belum Lunas</p>
        <a href="{{ route('siswa.payment.index') }}" style="font-size:11px;color:#d97706;text-decoration:none">Bayar sekarang →</a>
    </div>
</div>
@endif

{{-- Prakerin aktif --}}
@if($prakerinActive)
<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:14px 16px;margin-bottom:12px;display:flex;align-items:center;gap:12px">
    <div style="width:36px;height:36px;background:#dbeafe;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <svg width="16" height="16" fill="none" stroke="#3b82f6" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
    </div>
    <div style="flex:1">
        <p style="font-size:13.5px;font-weight:700;color:#1e40af;margin:0">Prakerin Aktif</p>
        <p style="font-size:11px;color:#3b82f6;margin:2px 0 0">{{ $prakerinActive->location->name }}</p>
    </div>
    <a href="{{ route('siswa.prakerin.index') }}" style="font-size:12px;font-weight:600;color:#1d4ed8;background:#dbeafe;padding:6px 14px;border-radius:8px;text-decoration:none">Buka →</a>
</div>
@endif

<a href="{{ route('siswa.attendance.history') }}"
   style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#3b82f6;font-weight:500;text-decoration:none;margin-top:4px">
    Riwayat absensi lengkap
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
</a>

{{-- ═══ GRAFIK AKADEMIK ═══ --}}
<style>
@media(max-width:767px){#chart-grid{grid-template-columns:1fr!important}}
</style>
<div id="chart-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:16px">

    {{-- Grafik Kehadiran 7 Hari --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px;box-shadow:0 1px 4px rgba(15,23,42,.06)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
            <div style="width:3px;height:16px;background:linear-gradient(180deg,#3b82f6,#1d4ed8);border-radius:2px"></div>
            <h3 style="font-size:13px;font-weight:700;color:#0f172a;margin:0">Kehadiran 7 Hari</h3>
        </div>
        <div style="display:flex;align-items:flex-end;gap:6px;height:80px">
            @foreach ($attendanceChart as $day)
                @php
                    $color = match($day['status']) {
                        'hadir'     => '#3b82f6',
                        'terlambat' => '#f59e0b',
                        'izin'      => '#8b5cf6',
                        'sakit'     => '#06b6d4',
                        'alfa'      => '#ef4444',
                        default     => '#e2e8f0',
                    };
                    $height = $day['status'] === 'none' ? 12 : ($day['hadir'] ? 80 : 40);
                    $opacity = $day['status'] === 'none' ? '0.4' : '1';
                @endphp
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px">
                    <div style="width:100%;border-radius:4px 4px 0 0;height:{{ $height }}px;background:{{ $color }};opacity:{{ $opacity }};transition:height .3s"
                         title="{{ $day['date'] }} - {{ $day['status'] }}"></div>
                    <span style="font-size:10px;color:#94a3b8;white-space:nowrap">{{ $day['day'] }}</span>
                </div>
            @endforeach
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px">
            @foreach ([['#3b82f6','Hadir'],['#f59e0b','Terlambat'],['#ef4444','Alfa'],['#8b5cf6','Izin'],['#e2e8f0','–']] as [$col,$lbl])
            <span style="display:flex;align-items:center;gap:3px;font-size:10px;color:#64748b">
                <span style="width:8px;height:8px;border-radius:2px;background:{{ $col }};flex-shrink:0"></span>{{ $lbl }}
            </span>
            @endforeach
        </div>
    </div>

    {{-- Grafik Nilai Per Mapel --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px;box-shadow:0 1px 4px rgba(15,23,42,.06)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
            <div style="width:3px;height:16px;background:linear-gradient(180deg,#10b981,#059669);border-radius:2px"></div>
            <h3 style="font-size:13px;font-weight:700;color:#0f172a;margin:0">Nilai Per Mapel</h3>
        </div>
        @if($scoreChart->isEmpty())
            <div style="text-align:center;padding:20px 0">
                <p style="font-size:12px;color:#94a3b8">Belum ada nilai</p>
            </div>
        @else
            <div style="display:flex;flex-direction:column;gap:8px">
                @foreach ($scoreChart as $mapel => $avg)
                    @php
                        $pct   = min(100, $avg);
                        $color = $avg >= 80 ? '#10b981' : ($avg >= 65 ? '#3b82f6' : '#f59e0b');
                    @endphp
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px">
                            <span style="font-size:11px;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%">{{ Str::limit($mapel, 18) }}</span>
                            <span style="font-size:11px;font-weight:700;color:{{ $color }}">{{ $avg }}</span>
                        </div>
                        <div style="height:6px;background:#f1f5f9;border-radius:20px">
                            <div style="height:6px;border-radius:20px;background:{{ $color }};width:{{ $pct }}%;transition:width .5s ease"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Nilai Terbaru Timeline --}}
@if($recentScores->isNotEmpty())
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin-top:14px;overflow:hidden;box-shadow:0 1px 4px rgba(15,23,42,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f1f5f9">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:3px;height:16px;background:linear-gradient(180deg,#6366f1,#4f46e5);border-radius:2px"></div>
            <h3 style="font-size:13px;font-weight:700;color:#0f172a;margin:0">Nilai Terbaru</h3>
        </div>
        <a href="{{ route('siswa.assignments.scores') }}" style="font-size:12px;color:#3b82f6;font-weight:600;text-decoration:none">Lihat semua →</a>
    </div>
    @foreach ($recentScores as $sub)
        @php
            $scoreColor = $sub->score >= 80 ? '#059669' : ($sub->score >= 65 ? '#2563eb' : '#d97706');
            $scoreBg    = $sub->score >= 80 ? '#ecfdf5' : ($sub->score >= 65 ? '#eff6ff' : '#fffbeb');
        @endphp
        <div style="display:flex;align-items:center;gap:12px;padding:11px 18px;border-bottom:1px solid #f8fafc">
            <div style="flex:1;min-width:0">
                <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $sub->assignment->title }}</p>
                <p style="font-size:11px;color:#94a3b8;margin:2px 0 0">{{ $sub->assignment->subject->name ?? '' }} · {{ $sub->graded_at?->diffForHumans() }}</p>
            </div>
            <div style="width:40px;height:40px;border-radius:10px;background:{{ $scoreBg }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <span style="font-size:14px;font-weight:800;color:{{ $scoreColor }}">{{ $sub->score }}</span>
            </div>
        </div>
    @endforeach
</div>
@endif

</x-simans-layout>
