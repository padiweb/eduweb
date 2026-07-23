<x-simans-layout title="Dashboard Kepala Sekolah">

<div style="margin-bottom:24px">
    <h1 style="font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;margin:0 0 4px">Dashboard Kepala Sekolah</h1>
    <p style="font-size:13px;color:#64748b;margin:0">{{ now()->translatedFormat('l, d F Y') }} &middot; {{ auth()->user()->school->name ?? '' }}</p>
</div>

<div id="stat-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:80px;height:80px;background:#eff6ff;border-radius:50%;opacity:.6"></div>
        <div style="width:36px;height:36px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#1e40af;letter-spacing:-1px;line-height:1">{{ $stats['total_siswa'] }}</div>
        <div style="font-size:13px;font-weight:600;color:#1e293b;margin-top:6px">Total Siswa Aktif</div>
    </div>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:80px;height:80px;background:#f5f3ff;border-radius:50%;opacity:.6"></div>
        <div style="width:36px;height:36px;background:#f5f3ff;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#7c3aed" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#6d28d9;letter-spacing:-1px;line-height:1">{{ $stats['total_guru'] }}</div>
        <div style="font-size:13px;font-weight:600;color:#1e293b;margin-top:6px">Total Guru</div>
    </div>

    <div style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);border-radius:16px;padding:20px;box-shadow:0 4px 14px rgba(59,130,246,0.4);position:relative;overflow:hidden">
        <div style="position:absolute;top:-18px;right:-18px;width:80px;height:80px;background:rgba(255,255,255,0.12);border-radius:50%"></div>
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="white" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:white;letter-spacing:-1px;line-height:1">{{ $stats['hadir_hari_ini'] }}</div>
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.9);margin-top:6px">Hadir Hari Ini</div>
    </div>

    <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:16px;padding:20px;box-shadow:0 4px 14px rgba(16,185,129,0.35);position:relative;overflow:hidden">
        <div style="position:absolute;top:-18px;right:-18px;width:80px;height:80px;background:rgba(255,255,255,0.12);border-radius:50%"></div>
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="white" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
        </div>
        <div style="font-size:18px;font-weight:800;color:white;line-height:1.2">Rp {{ number_format($pemasukanBulan,0,',','.') }}</div>
        <div style="font-size:12px;font-weight:600;color:rgba(255,255,255,.9);margin-top:6px">Pemasukan Bulan Ini</div>
    </div>

    <div style="background:#fff;border:1px solid #fecaca;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:80px;height:80px;background:#fff1f2;border-radius:50%;opacity:.6"></div>
        <div style="width:36px;height:36px;background:#fff1f2;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#dc2626" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#dc2626;letter-spacing:-1px;line-height:1">{{ $stats['tunggakan'] }}</div>
        <div style="font-size:13px;font-weight:600;color:#1e293b;margin-top:6px">Tunggakan</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">tagihan belum lunas</div>
    </div>

    <div style="background:#fff;border:1px solid #fed7aa;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:80px;height:80px;background:#fff7ed;border-radius:50%;opacity:.6"></div>
        <div style="width:36px;height:36px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#d97706" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#b45309;letter-spacing:-1px;line-height:1">{{ $stats['pelanggaran_bulan'] }}</div>
        <div style="font-size:13px;font-weight:600;color:#1e293b;margin-top:6px">Pelanggaran</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Bulan ini</div>
    </div>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 1px 4px rgba(15,23,42,0.06)">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:8px">
        <div style="width:3px;height:16px;background:linear-gradient(180deg,#3b82f6,#1d4ed8);border-radius:2px"></div>
        <h2 style="font-size:14px;font-weight:700;color:#0f172a;margin:0">Rekap Kelas Hari Ini</h2>
    </div>
    @forelse($classroomSummary as $classroom)
        @php
            $session = $classroom->attendanceSessions->first();
            $hadir   = $session?->attendances->whereIn('status', ['hadir','terlambat'])->count() ?? 0;
            $total   = $classroom->students->count();
            $pct     = $total > 0 ? round($hadir / $total * 100) : 0;
            $barColor = $pct >= 80 ? '#10b981' : ($pct >= 60 ? '#f59e0b' : '#ef4444');
        @endphp
        <div style="display:flex;align-items:center;gap:16px;padding:12px 20px;border-bottom:1px solid #f8fafc">
            <div style="flex:1;min-width:0">
                <p style="font-size:13.5px;font-weight:600;color:#1e293b;margin:0">{{ $classroom->name }}</p>
                <p style="font-size:11px;color:#94a3b8;margin:2px 0 0">{{ $classroom->major->code ?? '' }} · {{ $total }} siswa</p>
            </div>
            <div style="width:100px;background:#f1f5f9;border-radius:20px;height:6px">
                <div style="height:6px;border-radius:20px;background:{{ $barColor }};width:{{ $pct }}%;transition:width .3s"></div>
            </div>
            <span style="font-size:12px;font-weight:600;color:#64748b;flex-shrink:0;width:40px;text-align:right">{{ $hadir }}/{{ $total }}</span>
            @if(!$session)
                <span style="font-size:11px;padding:3px 10px;border-radius:20px;background:#f1f5f9;color:#94a3b8;font-weight:600;flex-shrink:0">Belum</span>
            @elseif($session->is_closed)
                <span style="font-size:11px;padding:3px 10px;border-radius:20px;background:#f1f5f9;color:#64748b;font-weight:600;flex-shrink:0">Selesai</span>
            @else
                <span style="font-size:11px;padding:3px 10px;border-radius:20px;background:#ecfdf5;color:#065f46;font-weight:600;flex-shrink:0">Aktif</span>
            @endif
        </div>
    @empty
        <div style="padding:32px;text-align:center;color:#94a3b8;font-size:13px">Belum ada kelas aktif.</div>
    @endforelse
</div>
</x-simans-layout>
