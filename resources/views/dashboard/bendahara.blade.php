<x-simans-layout title="Dashboard Keuangan">

<div style="margin-bottom:24px">
    <h1 style="font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;margin:0 0 4px">Dashboard Keuangan</h1>
    <p style="font-size:13px;color:#64748b;margin:0">{{ now()->translatedFormat('l, d F Y') }} &middot; {{ auth()->user()->school->name ?? '' }}</p>
</div>

<div id="stat-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">

    <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:16px;padding:20px;box-shadow:0 4px 14px rgba(16,185,129,0.35);position:relative;overflow:hidden">
        <div style="position:absolute;top:-18px;right:-18px;width:80px;height:80px;background:rgba(255,255,255,0.12);border-radius:50%"></div>
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="white" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
        </div>
        <div style="font-size:16px;font-weight:800;color:white;line-height:1.2">Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}</div>
        <div style="font-size:12px;font-weight:600;color:rgba(255,255,255,.9);margin-top:6px">Pemasukan Bulan Ini</div>
        <div style="font-size:11px;color:rgba(255,255,255,.6);margin-top:2px">Sudah diverifikasi</div>
    </div>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:70px;height:70px;background:#eff6ff;border-radius:50%;opacity:.6"></div>
        <div style="width:36px;height:36px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#1e40af;letter-spacing:-1px;line-height:1">{{ $stats['tagihan_bulan_ini'] }}</div>
        <div style="font-size:12px;font-weight:600;color:#334155;margin-top:6px">Tagihan Bulan Ini</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Total tagihan aktif</div>
    </div>

    <div style="background:#fff;border:1px solid #fed7aa;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:70px;height:70px;background:#fff7ed;border-radius:50%;opacity:.6"></div>
        <div style="width:36px;height:36px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#d97706" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#b45309;letter-spacing:-1px;line-height:1">{{ $stats['menunggu_konfirmasi'] }}</div>
        <div style="font-size:12px;font-weight:600;color:#334155;margin-top:6px">Menunggu Konfirmasi</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Transfer masuk</div>
    </div>

    <div style="background:#fff;border:1px solid #fecaca;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:70px;height:70px;background:#fff1f2;border-radius:50%;opacity:.6"></div>
        <div style="width:36px;height:36px;background:#fff1f2;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#dc2626" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#dc2626;letter-spacing:-1px;line-height:1">{{ $stats['total_tunggakan'] }}</div>
        <div style="font-size:12px;font-weight:600;color:#334155;margin-top:6px">Total Tunggakan</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Belum lunas</div>
    </div>
</div>

<div id="panel-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 1px 4px rgba(15,23,42,0.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f1f5f9">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:3px;height:16px;background:linear-gradient(180deg,#f59e0b,#d97706);border-radius:2px"></div>
                <h2 style="font-size:14px;font-weight:700;color:#0f172a;margin:0">Transfer Perlu Dikonfirmasi</h2>
            </div>
            <a href="{{ route('bendahara.bills.index') }}" style="font-size:12px;color:#3b82f6;font-weight:600;text-decoration:none">Lihat semua →</a>
        </div>
        @forelse($pendingTransfers as $tx)
            <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid #f8fafc">
                <div style="width:34px;height:34px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="14" height="14" fill="none" stroke="#d97706" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </div>
                <div style="flex:1;min-width:0">
                    <p style="font-size:13.5px;font-weight:600;color:#1e293b;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $tx->bill?->student?->name }}</p>
                    <p style="font-size:11px;color:#94a3b8;margin:2px 0 0">{{ $tx->bill?->paymentType?->name }}</p>
                </div>
                <span style="font-size:13px;font-weight:700;color:#b45309;flex-shrink:0">Rp {{ number_format($tx->amount,0,',','.') }}</span>
            </div>
        @empty
            <div style="padding:32px;text-align:center;color:#94a3b8;font-size:13px">Tidak ada transfer menunggu.</div>
        @endforelse
    </div>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
            <div style="width:3px;height:16px;background:linear-gradient(180deg,#dc2626,#b91c1c);border-radius:2px"></div>
            <h2 style="font-size:14px;font-weight:700;color:#0f172a;margin:0">Tunggakan Per Kelas</h2>
        </div>
        @forelse($tunggakanPerKelas as $kelas => $count)
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                <p style="flex:1;font-size:13px;color:#334155;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $kelas }}</p>
                <div style="flex:1;background:#f1f5f9;border-radius:20px;height:6px;max-width:96px">
                    <div style="height:6px;border-radius:20px;background:linear-gradient(90deg,#ef4444,#dc2626);width:{{ min(100,$count*5) }}%"></div>
                </div>
                <span style="font-size:12px;font-weight:700;color:#dc2626;width:24px;text-align:right;flex-shrink:0">{{ $count }}</span>
            </div>
        @empty
            <p style="color:#94a3b8;font-size:13px;text-align:center;padding:16px 0">Tidak ada tunggakan.</p>
        @endforelse
    </div>
</div>
</x-simans-layout>
