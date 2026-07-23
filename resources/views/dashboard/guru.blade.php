<x-simans-layout title="Dashboard">

<div style="margin-bottom:24px">
    <h1 style="font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;margin:0 0 4px">Selamat datang, {{ auth()->user()->name }}</h1>
    <p style="font-size:13px;color:#64748b;margin:0">{{ now()->translatedFormat('l, d F Y') }}</p>
</div>

{{-- Stat cards --}}
<div id="stat-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-14px;right:-14px;width:70px;height:70px;background:#eff6ff;border-radius:50%;opacity:.6"></div>
        <div style="width:34px;height:34px;background:#eff6ff;border-radius:9px;display:flex;align-items:center;justify-content:center;margin-bottom:10px">
            <svg width="16" height="16" fill="none" stroke="#3b82f6" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
        </div>
        <div style="font-size:28px;font-weight:800;color:#1e40af;letter-spacing:-1px;line-height:1">{{ $stats['total_kelas'] }}</div>
        <div style="font-size:12px;font-weight:600;color:#334155;margin-top:5px">Total Kelas</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Semester aktif</div>
    </div>

    <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:16px;padding:18px;box-shadow:0 4px 12px rgba(16,185,129,0.35);position:relative;overflow:hidden">
        <div style="position:absolute;top:-14px;right:-14px;width:70px;height:70px;background:rgba(255,255,255,0.15);border-radius:50%"></div>
        <div style="width:34px;height:34px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;margin-bottom:10px">
            <svg width="16" height="16" fill="none" stroke="white" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div style="font-size:28px;font-weight:800;color:white;letter-spacing:-1px;line-height:1">{{ $stats['total_hadir'] }}</div>
        <div style="font-size:12px;font-weight:600;color:rgba(255,255,255,.9);margin-top:5px">Hadir Hari Ini</div>
        <div style="font-size:11px;color:rgba(255,255,255,.6);margin-top:2px">Semua kelas</div>
    </div>

    <div style="background:#fff;border:1px solid #fecaca;border-radius:16px;padding:18px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-14px;right:-14px;width:70px;height:70px;background:#fff1f2;border-radius:50%;opacity:.6"></div>
        <div style="width:34px;height:34px;background:#fff1f2;border-radius:9px;display:flex;align-items:center;justify-content:center;margin-bottom:10px">
            <svg width="16" height="16" fill="none" stroke="#dc2626" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </div>
        <div style="font-size:28px;font-weight:800;color:#dc2626;letter-spacing:-1px;line-height:1">{{ $stats['total_alfa'] }}</div>
        <div style="font-size:12px;font-weight:600;color:#334155;margin-top:5px">Alfa Hari Ini</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Tanpa keterangan</div>
    </div>

    <div style="background:#fff;border:1px solid #fed7aa;border-radius:16px;padding:18px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-14px;right:-14px;width:70px;height:70px;background:#fff7ed;border-radius:50%;opacity:.6"></div>
        <div style="width:34px;height:34px;background:#fff7ed;border-radius:9px;display:flex;align-items:center;justify-content:center;margin-bottom:10px">
            <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
        </div>
        <div style="font-size:28px;font-weight:800;color:#b45309;letter-spacing:-1px;line-height:1">{{ $jurnalBulanIni }}</div>
        <div style="font-size:12px;font-weight:600;color:#334155;margin-top:5px">Jurnal Bulan Ini</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Jurnal mengajar</div>
    </div>
</div>

<div id="panel-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

    {{-- Status kelas hari ini --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 1px 4px rgba(15,23,42,0.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f1f5f9">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:3px;height:16px;background:linear-gradient(180deg,#10b981,#059669);border-radius:2px"></div>
                <h2 style="font-size:14px;font-weight:700;color:#0f172a;margin:0">Status Kelas Hari Ini</h2>
            </div>
            <a href="{{ route('guru.attendance.index') }}" style="font-size:12px;color:#3b82f6;font-weight:600;text-decoration:none">Kelola →</a>
        </div>
        @forelse($classrooms as $classroom)
            @php $session = $todaySessions->firstWhere('classroom_id', $classroom->id); @endphp
            <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid #f8fafc">
                <div style="flex:1;min-width:0">
                    <p style="font-size:13.5px;font-weight:600;color:#1e293b;margin:0">{{ $classroom->name }}</p>
                    <p style="font-size:11px;color:#94a3b8;margin:2px 0 0">{{ $classroom->major->code ?? '' }} · {{ $classroom->students->count() }} siswa</p>
                </div>
                @if($session)
                    <span style="font-size:11px;color:#64748b">{{ $session->attendances->count() }} absen</span>
                    @if($session->is_closed)
                        <span style="font-size:11px;padding:3px 10px;border-radius:20px;background:#f1f5f9;color:#64748b;font-weight:600">Tutup</span>
                    @else
                        <span style="font-size:11px;padding:3px 10px;border-radius:20px;background:#ecfdf5;color:#065f46;font-weight:600;display:flex;align-items:center;gap:4px">
                            <span style="width:6px;height:6px;border-radius:50%;background:#10b981;animation:pulse 2s infinite"></span>Aktif
                        </span>
                    @endif
                @else
                    <form method="POST" action="{{ route('guru.attendance.open') }}">
                        @csrf
                        <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                        <button type="submit" style="font-size:12px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;font-weight:600;padding:6px 14px;border-radius:8px;border:none;cursor:pointer;box-shadow:0 2px 6px rgba(59,130,246,.3)">Buka</button>
                    </form>
                @endif
            </div>
        @empty
            <div style="padding:32px;text-align:center;color:#94a3b8;font-size:13px">Belum ada kelas aktif.</div>
        @endforelse
    </div>

    {{-- Quick access --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
            <div style="width:3px;height:16px;background:linear-gradient(180deg,#3b82f6,#1d4ed8);border-radius:2px"></div>
            <h2 style="font-size:14px;font-weight:700;color:#0f172a;margin:0">Akses Cepat</h2>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
            <a href="{{ route('guru.journal.index') }}" style="display:flex;align-items:center;gap:12px;padding:13px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;text-decoration:none" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe'" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'">
                <div style="width:36px;height:36px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                </div>
                <span style="font-size:13.5px;font-weight:500;color:#334155">Jurnal Mengajar</span>
            </a>
            <a href="{{ route('guru.assignments.index') }}" style="display:flex;align-items:center;gap:12px;padding:13px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;text-decoration:none" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe'" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'">
                <div style="width:36px;height:36px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="16" height="16" fill="none" stroke="#3b82f6" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                </div>
                <span style="font-size:13.5px;font-weight:500;color:#334155">Tugas & Nilai</span>
            </a>
            <a href="{{ route('guru.teacher-attendance.index') }}" style="display:flex;align-items:center;gap:12px;padding:13px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;text-decoration:none" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe'" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'">
                <div style="width:36px;height:36px;background:#ecfdf5;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="16" height="16" fill="none" stroke="#059669" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span style="font-size:13.5px;font-weight:500;color:#334155">Absensi Saya</span>
            </a>
            @if($prakerinCount > 0)
            <a href="{{ route('guru.prakerin.index') }}" style="display:flex;align-items:center;gap:12px;padding:13px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;text-decoration:none">
                <div style="width:36px;height:36px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                </div>
                <div>
                    <div style="font-size:13.5px;font-weight:500;color:#334155">Koordinator Prakerin</div>
                    <div style="font-size:11px;color:#d97706">{{ $prakerinCount }} siswa bimbingan</div>
                </div>
            </a>
            @endif
        </div>
    </div>
</div>
<style>@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}</style>
</x-simans-layout>
