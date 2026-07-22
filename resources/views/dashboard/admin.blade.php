<x-simans-layout title="Dashboard">

{{-- Page header --}}
<div style="margin-bottom:24px">
    <h1 style="font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;margin:0 0 4px">Dashboard Admin</h1>
    <p style="font-size:13px;color:#64748b;margin:0">{{ now()->translatedFormat('l, d F Y') }} &middot; {{ auth()->user()->school->name ?? 'SiManS' }}</p>
</div>

{{-- Stat cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">

    {{-- Card 1 - Siswa --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:80px;height:80px;background:#eff6ff;border-radius:50%;opacity:.7"></div>
        <div style="width:36px;height:36px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#1e40af;letter-spacing:-1px;line-height:1">{{ $stats['siswa'] }}</div>
        <div style="font-size:13px;font-weight:600;color:#1e293b;margin-top:6px">Total Siswa Aktif</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Terdaftar di sekolah</div>
    </div>

    {{-- Card 2 - Guru --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:80px;height:80px;background:#f5f3ff;border-radius:50%;opacity:.7"></div>
        <div style="width:36px;height:36px;background:#f5f3ff;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#7c3aed" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#6d28d9;letter-spacing:-1px;line-height:1">{{ $stats['guru'] }}</div>
        <div style="font-size:13px;font-weight:600;color:#1e293b;margin-top:6px">Total Guru</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Termasuk wali kelas</div>
    </div>

    {{-- Card 3 - Kelas --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:80px;height:80px;background:#ecfdf5;border-radius:50%;opacity:.7"></div>
        <div style="width:36px;height:36px;background:#ecfdf5;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#059669" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#065f46;letter-spacing:-1px;line-height:1">{{ $stats['kelas'] }}</div>
        <div style="font-size:13px;font-weight:600;color:#1e293b;margin-top:6px">Kelas Aktif</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Tahun ajaran aktif</div>
    </div>

    {{-- Card 4 - Hadir (biru solid) --}}
    <div style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);border-radius:16px;padding:20px;box-shadow:0 4px 14px rgba(59,130,246,0.4);position:relative;overflow:hidden">
        <div style="position:absolute;top:-20px;right:-20px;width:90px;height:90px;background:rgba(255,255,255,0.12);border-radius:50%"></div>
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="white" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:white;letter-spacing:-1px;line-height:1">{{ $stats['hadir_hari_ini'] }}</div>
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.9);margin-top:6px">Hadir Hari Ini</div>
        <div style="font-size:11px;color:rgba(255,255,255,0.6);margin-top:2px">Hadir + terlambat</div>
    </div>

    {{-- Card 5 - Alfa --}}
    <div style="background:#fff;border:1px solid #fecaca;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:80px;height:80px;background:#fff1f2;border-radius:50%;opacity:.7"></div>
        <div style="width:36px;height:36px;background:#fff1f2;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#dc2626" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#dc2626;letter-spacing:-1px;line-height:1">{{ $stats['alfa_hari_ini'] }}</div>
        <div style="font-size:13px;font-weight:600;color:#1e293b;margin-top:6px">Alfa Hari Ini</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Tanpa keterangan</div>
    </div>

    {{-- Card 6 - Tunggakan --}}
    <div style="background:#fff;border:1px solid #fed7aa;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden">
        <div style="position:absolute;top:-16px;right:-16px;width:80px;height:80px;background:#fff7ed;border-radius:50%;opacity:.7"></div>
        <div style="width:36px;height:36px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
            <svg width="18" height="18" fill="none" stroke="#d97706" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
        </div>
        <div style="font-size:32px;font-weight:800;color:#b45309;letter-spacing:-1px;line-height:1">{{ $stats['tunggakan'] }}</div>
        <div style="font-size:13px;font-weight:600;color:#1e293b;margin-top:6px">Tunggakan SPP</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px">Belum lunas</div>
    </div>
</div>

{{-- Bottom panels --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

    {{-- Absensi hari ini --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 1px 4px rgba(15,23,42,0.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f1f5f9">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:3px;height:16px;background:linear-gradient(180deg,#3b82f6,#1d4ed8);border-radius:2px"></div>
                <h2 style="font-size:14px;font-weight:700;color:#0f172a;margin:0">Absensi Hari Ini</h2>
            </div>
            <a href="{{ route('admin.teacher-attendance.index') }}" style="font-size:12px;color:#3b82f6;font-weight:600;text-decoration:none">Lihat semua →</a>
        </div>
        @forelse($recentSessions as $session)
            <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid #f8fafc">
                <div style="flex:1;min-width:0">
                    <p style="font-size:13.5px;font-weight:600;color:#1e293b;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $session->classroom->name }}</p>
                    <p style="font-size:11px;color:#94a3b8;margin:2px 0 0">{{ $session->openedBy?->name ?? 'Otomatis' }} · {{ $session->created_at->format('H:i') }}</p>
                </div>
                <span style="font-size:11px;color:#64748b;flex-shrink:0">{{ $session->attendances->count() }} siswa</span>
                @if($session->is_closed)
                    <span style="font-size:11px;padding:3px 10px;border-radius:20px;background:#f1f5f9;color:#64748b;font-weight:600;flex-shrink:0">Tutup</span>
                @else
                    <span style="font-size:11px;padding:3px 10px;border-radius:20px;background:#eff6ff;color:#1d4ed8;font-weight:600;flex-shrink:0;display:flex;align-items:center;gap:4px">
                        <span style="width:6px;height:6px;border-radius:50%;background:#3b82f6;animation:pulse 2s infinite"></span>Aktif
                    </span>
                @endif
            </div>
        @empty
            <div style="padding:32px;text-align:center;color:#94a3b8;font-size:13px">Belum ada sesi absensi hari ini.</div>
        @endforelse
    </div>

    {{-- Akses Cepat --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(15,23,42,0.06)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
            <div style="width:3px;height:16px;background:linear-gradient(180deg,#3b82f6,#1d4ed8);border-radius:2px"></div>
            <h2 style="font-size:14px;font-weight:700;color:#0f172a;margin:0">Akses Cepat</h2>
        </div>
        @php
            $menus = [
                ['route'=>'admin.users.index',      'label'=>'Manajemen User',  'color'=>'#3b82f6','bg'=>'#eff6ff', 'icon'=>'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
                ['route'=>'admin.classrooms.index', 'label'=>'Kelas',           'color'=>'#7c3aed','bg'=>'#f5f3ff', 'icon'=>'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
                ['route'=>'admin.schedules.index',  'label'=>'Jadwal Mengajar', 'color'=>'#0891b2','bg'=>'#ecfeff', 'icon'=>'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
                ['route'=>'admin.subjects.index',   'label'=>'Mata Pelajaran',  'color'=>'#059669','bg'=>'#ecfdf5', 'icon'=>'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25'],
                ['route'=>'admin.settings.school',  'label'=>'Pengaturan',      'color'=>'#64748b','bg'=>'#f8fafc', 'icon'=>'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                ['route'=>'admin.promotions.index', 'label'=>'Promosi Siswa',   'color'=>'#dc2626','bg'=>'#fff1f2', 'icon'=>'M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18'],
            ];
        @endphp
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            @foreach($menus as $menu)
            <a href="{{ route($menu['route']) }}"
               style="display:flex;align-items:center;gap:10px;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;text-decoration:none;transition:all .15s"
               onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe'"
               onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'">
                <div style="width:32px;height:32px;background:{{ $menu['bg'] }};border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="15" height="15" fill="none" stroke="{{ $menu['color'] }}" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $menu['icon'] }}"/>
                    </svg>
                </div>
                <span style="font-size:12.5px;font-weight:500;color:#334155">{{ $menu['label'] }}</span>
            </a>
            @endforeach
        </div>
    </div>
</div>

<style>@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}</style>
</x-simans-layout>
