<x-simans-layout title="Dashboard Kesiswaan">

<div style="margin-bottom:24px">
    <h1 style="font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;margin:0 0 4px">Dashboard Kesiswaan</h1>
    <p style="font-size:13px;color:#64748b;margin:0">{{ now()->translatedFormat('l, d F Y') }}</p>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">

    <div style="background:#fff;border:1px solid #fecaca;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:70px;height:70px;background:#fff1f2;border-radius:50%;opacity:.6"></div>
        <div style="width:36px;height:36px;background:#fff1f2;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#dc2626" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#dc2626;letter-spacing:-1px;line-height:1">{{ $stats['alfa_hari_ini'] }}</div>
        <div style="font-size:12px;font-weight:600;color:#334155;margin-top:6px">Alfa Hari Ini</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Tanpa keterangan</div>
    </div>

    <div style="background:#fff;border:1px solid #fed7aa;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:70px;height:70px;background:#fff7ed;border-radius:50%;opacity:.6"></div>
        <div style="width:36px;height:36px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#d97706" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#b45309;letter-spacing:-1px;line-height:1">{{ $stats['terlambat_hari_ini'] }}</div>
        <div style="font-size:12px;font-weight:600;color:#334155;margin-top:6px">Terlambat</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Hari ini</div>
    </div>

    <div style="background:linear-gradient(135deg,#f97316 0%,#ea580c 100%);border-radius:16px;padding:20px;box-shadow:0 4px 14px rgba(249,115,22,0.35);position:relative;overflow:hidden">
        <div style="position:absolute;top:-18px;right:-18px;width:70px;height:70px;background:rgba(255,255,255,0.12);border-radius:50%"></div>
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="white" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:white;letter-spacing:-1px;line-height:1">{{ $stats['pelanggaran_bulan_ini'] }}</div>
        <div style="font-size:12px;font-weight:600;color:rgba(255,255,255,.9);margin-top:6px">Pelanggaran</div>
        <div style="font-size:11px;color:rgba(255,255,255,.6);margin-top:2px">Bulan ini</div>
    </div>

    <div style="background:#fff;border:1px solid #fecaca;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:70px;height:70px;background:#fff1f2;border-radius:50%;opacity:.6"></div>
        <div style="width:36px;height:36px;background:#fff1f2;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#dc2626" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#be123c;letter-spacing:-1px;line-height:1">{{ $stats['siswa_bermasalah'] }}</div>
        <div style="font-size:12px;font-weight:600;color:#334155;margin-top:6px">Siswa Bermasalah</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Poin ≥ 50</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 1px 4px rgba(15,23,42,0.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f1f5f9">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:3px;height:16px;background:linear-gradient(180deg,#dc2626,#b91c1c);border-radius:2px"></div>
                <h2 style="font-size:14px;font-weight:700;color:#0f172a;margin:0">Pelanggaran Terbaru</h2>
            </div>
            <a href="{{ route('kesiswaan.violations.index') }}" style="font-size:12px;color:#3b82f6;font-weight:600;text-decoration:none">Lihat semua →</a>
        </div>
        @forelse($recentViolations as $v)
            <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid #f8fafc">
                <div style="width:34px;height:34px;background:#fff1f2;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="14" height="14" fill="none" stroke="#dc2626" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </div>
                <div style="flex:1;min-width:0">
                    <p style="font-size:13.5px;font-weight:600;color:#1e293b;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $v->student->name }}</p>
                    <p style="font-size:11px;color:#94a3b8;margin:2px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $v->category->name ?? '—' }}</p>
                </div>
                <span style="font-size:11px;padding:3px 10px;border-radius:20px;background:#fff1f2;color:#dc2626;font-weight:700;flex-shrink:0">+{{ $v->points }}</span>
                <span style="font-size:11px;color:#94a3b8;flex-shrink:0">{{ $v->created_at->diffForHumans() }}</span>
            </div>
        @empty
            <div style="padding:32px;text-align:center;color:#94a3b8;font-size:13px">Tidak ada pelanggaran bulan ini.</div>
        @endforelse
    </div>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
            <div style="width:3px;height:16px;background:linear-gradient(180deg,#f97316,#ea580c);border-radius:2px"></div>
            <h2 style="font-size:14px;font-weight:700;color:#0f172a;margin:0">Aksi Cepat</h2>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
            <a href="{{ route('kesiswaan.violations.index') }}" style="display:flex;align-items:center;gap:12px;padding:14px;background:#fff1f2;border:1px solid #fecaca;border-radius:12px;text-decoration:none">
                <div style="width:36px;height:36px;background:#fee2e2;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="16" height="16" fill="none" stroke="#dc2626" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <span style="font-size:13.5px;font-weight:600;color:#dc2626">Catat Pelanggaran</span>
            </a>
            <a href="{{ route('kesiswaan.violations.categories') }}" style="display:flex;align-items:center;gap:12px;padding:14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;text-decoration:none" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe'" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'">
                <div style="width:36px;height:36px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="16" height="16" fill="none" stroke="#3b82f6" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/></svg>
                </div>
                <span style="font-size:13.5px;font-weight:500;color:#334155">Kategori Pelanggaran</span>
            </a>
        </div>
    </div>
</div>
</x-simans-layout>
