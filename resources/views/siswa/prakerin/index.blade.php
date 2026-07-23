<x-simans-layout title="Prakerin">

<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900">Praktik Kerja Industri</h1>
    <p class="text-gray-500 text-sm mt-0.5">Absensi & Jurnal Harian</p>
</div>

@foreach (['success','error','info'] as $msg)
    @if (session($msg))
        <div class="mb-4 p-3 rounded-xl text-sm border
            {{ $msg === 'success' ? 'bg-blue-50 border-blue-200 text-blue-700' :
               ($msg === 'error'   ? 'bg-red-50 border-red-200 text-red-700' :
               'bg-blue-50 border-blue-200 text-blue-700') }}">
            {{ session($msg) }}
        </div>
    @endif
@endforeach

@if (! $placement)
    <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
        <p class="text-gray-500">Anda belum memiliki penempatan prakerin aktif hari ini.</p>
        <p class="text-gray-500 text-sm mt-1">Hubungi admin/wali kelas.</p>
    </div>
@else

    {{-- Info DU/DI --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px;margin-bottom:16px;box-shadow:0 1px 4px rgba(15,23,42,0.06)">
        <div style="display:flex;align-items:flex-start;gap:12px">
            <div style="width:40px;height:40px;border-radius:10px;background:#eff6ff;border:1px solid #bfdbfe;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="20" height="20" fill="none" stroke="#3b82f6" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                </svg>
            </div>
            <div style="flex:1;min-width:0">
                <p style="font-size:14px;font-weight:700;color:#0f172a;margin:0">{{ $placement->location->name }}</p>
                @if ($placement->location->address)
                    <p style="font-size:12px;color:#64748b;margin:3px 0 0">{{ $placement->location->address }}</p>
                @endif
                <p style="font-size:12px;color:#64748b;margin:4px 0 0">
                    {{ $placement->period->name }}
                    @if ($placement->location->checkin_time) · Masuk: {{ $placement->location->checkin_time }} @endif
                    @if ($placement->location->checkout_time) · Pulang: {{ $placement->location->checkout_time }} @endif
                </p>
                @if ($placement->location->supervisors->count() > 0)
                    <p style="font-size:12px;color:#64748b;margin:2px 0 0">Pembimbing: {{ $placement->location->supervisors->pluck('name')->join(', ') }}</p>
                @endif
            </div>
        </div>
    </div>

    <p style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px">
        Hari ini — {{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}
    </p>

    {{-- 4 Card aksi --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">

        {{-- Card Masuk --}}
        @php
            $cardMasukBg    = $checkin ? '#eff6ff' : '#f8fafc';
            $cardMasukBdr   = $checkin ? '#bfdbfe' : '#e2e8f0';
            $iconMasukBg    = $checkin ? '#dbeafe' : '#f1f5f9';
            $iconMasukColor = $checkin ? '#2563eb' : '#94a3b8';
        @endphp
        <div style="background:{{ $cardMasukBg }};border:1.5px solid {{ $cardMasukBdr }};border-radius:14px;padding:20px 16px;text-align:center;transition:box-shadow .2s">
            <div style="width:44px;height:44px;border-radius:12px;background:{{ $iconMasukBg }};display:flex;align-items:center;justify-content:center;margin:0 auto 10px">
                <svg width="22" height="22" fill="none" stroke="{{ $iconMasukColor }}" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
            </div>
            <p style="font-size:12px;font-weight:600;color:#64748b;margin:0 0 6px">Masuk</p>
            @if ($checkin)
                <p style="font-size:16px;font-weight:800;color:#1d4ed8;margin:0">{{ $checkin->selfie_taken_at?->format('H:i') ?? $checkin->created_at->format('H:i') }}</p>
                <p style="font-size:11px;color:#64748b;margin:2px 0 0">{{ $checkin->status_label }}</p>
            @elseif ($absence)
                <p style="font-size:13px;color:#94a3b8;margin:0">—</p>
            @else
                <a href="{{ route('siswa.prakerin.absen', 'check_in') }}"
                   style="display:inline-flex;align-items:center;gap:5px;margin-top:4px;padding:7px 16px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;font-size:12px;font-weight:700;border-radius:8px;text-decoration:none;box-shadow:0 2px 8px rgba(59,130,246,0.35)">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Absen
                </a>
            @endif
        </div>

        {{-- Card Pulang --}}
        @php
            $cardPulangBg    = $checkout ? '#eff6ff' : '#f8fafc';
            $cardPulangBdr   = $checkout ? '#bfdbfe' : '#e2e8f0';
            $iconPulangBg    = $checkout ? '#dbeafe' : '#f1f5f9';
            $iconPulangColor = $checkout ? '#2563eb' : '#94a3b8';
        @endphp
        <div style="background:{{ $cardPulangBg }};border:1.5px solid {{ $cardPulangBdr }};border-radius:14px;padding:20px 16px;text-align:center">
            <div style="width:44px;height:44px;border-radius:12px;background:{{ $iconPulangBg }};display:flex;align-items:center;justify-content:center;margin:0 auto 10px">
                <svg width="22" height="22" fill="none" stroke="{{ $iconPulangColor }}" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
            <p style="font-size:12px;font-weight:600;color:#64748b;margin:0 0 6px">Pulang</p>
            @if ($checkout)
                <p style="font-size:16px;font-weight:800;color:#1d4ed8;margin:0">{{ $checkout->selfie_taken_at?->format('H:i') ?? $checkout->created_at->format('H:i') }}</p>
            @elseif ($absence)
                <p style="font-size:13px;color:#94a3b8;margin:0">—</p>
            @else
                <a href="{{ route('siswa.prakerin.absen', 'check_out') }}"
                   style="display:inline-flex;align-items:center;gap:5px;margin-top:4px;padding:7px 16px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;font-size:12px;font-weight:700;border-radius:8px;text-decoration:none;box-shadow:0 2px 8px rgba(59,130,246,0.35)">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Absen
                </a>
            @endif
        </div>

        {{-- Card Jurnal --}}
        @php
            $cardJurnalBg    = $journal ? '#fffbeb' : '#f8fafc';
            $cardJurnalBdr   = $journal ? '#fde68a' : '#e2e8f0';
            $iconJurnalBg    = $journal ? '#fef3c7' : '#f1f5f9';
            $iconJurnalColor = $journal ? '#d97706' : '#94a3b8';
        @endphp
        <div style="background:{{ $cardJurnalBg }};border:1.5px solid {{ $cardJurnalBdr }};border-radius:14px;padding:20px 16px;text-align:center">
            <div style="width:44px;height:44px;border-radius:12px;background:{{ $iconJurnalBg }};display:flex;align-items:center;justify-content:center;margin:0 auto 10px">
                <svg width="22" height="22" fill="none" stroke="{{ $iconJurnalColor }}" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <p style="font-size:12px;font-weight:600;color:#64748b;margin:0 0 6px">Jurnal</p>
            @if ($journal)
                <p style="font-size:16px;font-weight:800;color:#d97706;margin:0">{{ $journal->submitted_at?->format('H:i') ?? $journal->updated_at->format('H:i') }}</p>
                <a href="{{ route('siswa.prakerin.jurnal') }}"
                   style="display:inline-block;margin-top:4px;font-size:11px;color:#d97706;font-weight:600;text-decoration:none">Edit →</a>
            @else
                <a href="{{ route('siswa.prakerin.jurnal') }}"
                   style="display:inline-flex;align-items:center;gap:5px;margin-top:4px;padding:7px 16px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-size:12px;font-weight:700;border-radius:8px;text-decoration:none;box-shadow:0 2px 8px rgba(245,158,11,0.35)">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Isi
                </a>
            @endif
        </div>

        {{-- Card Tidak Hadir --}}
        @php
            $absenceCard = \App\Models\PrakerinAbsence::where('placement_id', $placement->id)->where('absence_date', $today)->first();
            $cardTHBg    = $absenceCard ? '#fff7ed' : '#f8fafc';
            $cardTHBdr   = $absenceCard ? '#fed7aa' : '#e2e8f0';
            $iconTHBg    = $absenceCard ? '#ffedd5' : '#f1f5f9';
            $iconTHColor = $absenceCard ? '#ea580c' : '#94a3b8';
        @endphp
        <div style="background:{{ $cardTHBg }};border:1.5px solid {{ $cardTHBdr }};border-radius:14px;padding:20px 16px;text-align:center">
            <div style="width:44px;height:44px;border-radius:12px;background:{{ $iconTHBg }};display:flex;align-items:center;justify-content:center;margin:0 auto 10px">
                <svg width="22" height="22" fill="none" stroke="{{ $iconTHColor }}" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                </svg>
            </div>
            <p style="font-size:12px;font-weight:600;color:#64748b;margin:0 0 6px">Tidak Hadir</p>
            @if ($absenceCard)
                <p style="font-size:13px;font-weight:700;color:#ea580c;margin:0">{{ $absenceCard->type_label }}</p>
                <p style="font-size:11px;color:#2563eb;margin:2px 0 0;font-weight:600">Tercatat</p>
            @elseif (! $checkin)
                <a href="{{ route('siswa.prakerin.izin') }}"
                   style="display:inline-flex;align-items:center;gap:5px;margin-top:4px;padding:7px 16px;background:linear-gradient(135deg,#f97316,#ea580c);color:#fff;font-size:12px;font-weight:700;border-radius:8px;text-decoration:none;box-shadow:0 2px 8px rgba(249,115,22,0.32)">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Lapor
                </a>
            @else
                <p style="font-size:13px;color:#94a3b8;margin:0">—</p>
            @endif
        </div>
    </div>

    @if (! $journal)
        <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;margin-bottom:16px">
            <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="1.75" viewBox="0 0 24 24" style="flex-shrink:0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p style="font-size:12.5px;color:#b45309;margin:0">Jangan lupa isi jurnal harian! Batas waktu sampai <strong>23:59 malam ini</strong>.</p>
        </div>
    @endif

    @if ($recentLogs->count() > 0)
        <p style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px">7 Hari Terakhir</p>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 4px rgba(15,23,42,0.06)">
            @foreach ($recentLogs as $date => $logs)
                @php $ci = $logs->firstWhere('type','check_in'); $co = $logs->firstWhere('type','check_out'); @endphp
                <div style="display:flex;align-items:center;gap:12px;padding:11px 16px;border-bottom:1px solid #f1f5f9">
                    <p style="font-size:12px;color:#64748b;width:80px;flex-shrink:0">{{ \Carbon\Carbon::parse($date)->translatedFormat('D, d M') }}</p>
                    <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:{{ $ci ? '#eff6ff' : '#f8fafc' }};color:{{ $ci ? '#1d4ed8' : '#94a3b8' }}">
                        {{ $ci ? 'Masuk '.($ci->selfie_taken_at?->format('H:i') ?? $ci->created_at->format('H:i')) : '— Masuk' }}
                    </span>
                    <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:{{ $co ? '#eff6ff' : '#f8fafc' }};color:{{ $co ? '#1d4ed8' : '#94a3b8' }}">
                        {{ $co ? 'Pulang '.($co->selfie_taken_at?->format('H:i') ?? $co->created_at->format('H:i')) : '— Pulang' }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    <div style="margin-top:16px;text-align:center">
        <a href="{{ route('siswa.prakerin.jurnal.history') }}" style="font-size:13px;color:#3b82f6;font-weight:500;text-decoration:none">
            Lihat semua jurnal →
        </a>
    </div>
@endif

</x-simans-layout>
