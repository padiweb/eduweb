<x-simans-layout title="Profil Saya">

<div style="max-width:680px;margin:0 auto">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px">
        <div>
            <h1 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 4px">Profil Saya</h1>
            <p style="font-size:13px;color:#64748b;margin:0">Data lengkap siswa</p>
        </div>
        <a href="{{ route('siswa.profile.edit') }}"
           style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;box-shadow:0 2px 8px rgba(59,130,246,.28)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125L18 8.625"/>
            </svg>
            Edit Profil
        </a>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:12px;font-size:13px;font-weight:500;margin-bottom:20px;display:flex;align-items:center;gap:8px">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Kartu identitas utama --}}
    <div style="background:linear-gradient(135deg,#1e293b 0%,#334155 100%);border-radius:16px;padding:24px;margin-bottom:16px;display:flex;align-items:center;gap:20px;box-shadow:0 4px 20px rgba(15,23,42,.2)">
        {{-- Avatar / foto --}}
        @php $photo = $detail?->photo_path ? Storage::url($detail->photo_path) : null; @endphp
        <div style="width:80px;height:80px;border-radius:50%;overflow:hidden;border:3px solid rgba(255,255,255,.25);flex-shrink:0;background:linear-gradient(135deg,#3b82f6,#2563eb);display:flex;align-items:center;justify-content:center">
            @if($photo)
                <img src="{{ $photo }}" style="width:100%;height:100%;object-fit:cover" alt="Foto profil">
            @else
                <span style="font-size:28px;font-weight:700;color:#fff">{{ strtoupper(substr($user->name,0,2)) }}</span>
            @endif
        </div>
        {{-- Info utama --}}
        <div style="flex:1;min-width:0">
            <h2 style="font-size:20px;font-weight:800;color:#fff;margin:0 0 4px">{{ $user->name }}</h2>
            <div style="display:flex;flex-wrap:wrap;gap:8px 16px">
                <span style="font-size:12px;color:#94a3b8">NIS: <span style="color:#e2e8f0;font-weight:600">{{ $user->nis ?? '—' }}</span></span>
                @if($detail?->gender)
                    <span style="font-size:12px;color:#94a3b8">{{ $detail->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                @endif
                @php $kelas = $user->classrooms()->whereHas('academicYear', fn($q) => $q->where('is_active', true))->first(); @endphp
                @if($kelas)
                    <span style="font-size:12px;background:rgba(59,130,246,.25);color:#93c5fd;padding:2px 10px;border-radius:20px;font-weight:600">{{ $kelas->name }}</span>
                @endif
            </div>
            @if(!$photo && !$detail?->birth_date)
                <p style="font-size:11.5px;color:#f59e0b;margin:8px 0 0;display:flex;align-items:center;gap:5px">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    Profil belum lengkap — klik Edit Profil untuk melengkapi
                </p>
            @endif
        </div>
    </div>

    {{-- Grid info --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px" class="info-grid">

        {{-- Data Pribadi --}}
        <div style="grid-column:1/-1;background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.05)">
            <div style="padding:14px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between">
                <h3 style="font-size:13px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center;gap:7px">
                    <div style="width:4px;height:16px;background:linear-gradient(180deg,#3b82f6,#2563eb);border-radius:2px"></div>
                    Data Pribadi
                </h3>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;padding:4px 0" class="detail-grid">
                @php
                    $rows = [
                        ['Email',           $user->email],
                        ['Nomor HP/WA',     $user->phone ?: ($detail?->whatsapp ?: null)],
                        ['Jenis Kelamin',   $detail?->gender === 'L' ? 'Laki-laki' : ($detail?->gender === 'P' ? 'Perempuan' : null)],
                        ['Agama',           $detail?->religion],
                        ['Tempat Lahir',    $detail?->birth_place],
                        ['Tanggal Lahir',   $detail?->birth_date?->translatedFormat('d F Y')],
                        ['NIK',             $detail?->nik],
                        ['No. KK',          $detail?->no_kk],
                    ];
                @endphp
                @foreach($rows as [$label, $value])
                    <div style="padding:10px 20px;border-bottom:1px solid #f8fafc">
                        <p style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 2px">{{ $label }}</p>
                        <p style="font-size:13.5px;font-weight:500;color:{{ $value ? '#1e293b' : '#d1d5db' }};margin:0">
                            {{ $value ?: 'Belum diisi' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Alamat --}}
        <div style="grid-column:1/-1;background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.05)">
            <div style="padding:14px 20px;border-bottom:1px solid #f1f5f9">
                <h3 style="font-size:13px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center;gap:7px">
                    <div style="width:4px;height:16px;background:linear-gradient(180deg,#10b981,#059669);border-radius:2px"></div>
                    Alamat
                </h3>
            </div>
            <div style="padding:16px 20px">
                @if($detail?->is_abroad)
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px" class="detail-grid">
                        <div>
                            <p style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 2px">Negara</p>
                            <p style="font-size:13.5px;color:#1e293b;margin:0">{{ $detail->country ?: 'Belum diisi' }}</p>
                        </div>
                        <div>
                            <p style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 2px">Alamat</p>
                            <p style="font-size:13.5px;color:#1e293b;margin:0">{{ $detail->street ?: 'Belum diisi' }}</p>
                        </div>
                    </div>
                @elseif($detail && ($detail->province || $detail->regency || $detail->address))
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px" class="detail-grid">
                        @foreach([
                            ['Provinsi',        $detail->province],
                            ['Kab/Kota',        $detail->regency],
                            ['Kecamatan',       $detail->district],
                            ['Kelurahan/Desa',  $detail->village],
                        ] as [$lbl, $val])
                            <div>
                                <p style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 2px">{{ $lbl }}</p>
                                <p style="font-size:13.5px;color:{{ $val ? '#1e293b' : '#d1d5db' }};margin:0">{{ $val ?: 'Belum diisi' }}</p>
                            </div>
                        @endforeach
                        @if($detail->street)
                            <div style="grid-column:1/-1">
                                <p style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 2px">Jalan/Dusun/RT</p>
                                <p style="font-size:13.5px;color:#1e293b;margin:0">{{ $detail->street }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <p style="font-size:13px;color:#d1d5db;margin:0">Alamat belum diisi</p>
                @endif
            </div>
        </div>

        {{-- Orang Tua --}}
        <div style="grid-column:1/-1;background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.05)">
            <div style="padding:14px 20px;border-bottom:1px solid #f1f5f9">
                <h3 style="font-size:13px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center;gap:7px">
                    <div style="width:4px;height:16px;background:linear-gradient(180deg,#f59e0b,#d97706);border-radius:2px"></div>
                    Data Orang Tua / Wali
                </h3>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;padding:4px 0" class="detail-grid-3">
                @foreach([
                    ['Nama Ayah',   $detail?->father_name],
                    ['Nama Ibu',    $detail?->mother_name],
                    ['WA Orang Tua',$detail?->parent_whatsapp],
                ] as [$lbl, $val])
                    <div style="padding:12px 20px;border-bottom:1px solid #f8fafc">
                        <p style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 2px">{{ $lbl }}</p>
                        <p style="font-size:13.5px;font-weight:500;color:{{ $val ? '#1e293b' : '#d1d5db' }};margin:0">{{ $val ?: 'Belum diisi' }}</p>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Tombol edit di bawah juga --}}
    <div style="margin-top:20px;padding-bottom:40px;text-align:center">
        <a href="{{ route('siswa.profile.edit') }}"
           style="display:inline-flex;align-items:center;gap:7px;padding:10px 24px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;box-shadow:0 2px 8px rgba(59,130,246,.28)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
            </svg>
            Edit Profil
        </a>
    </div>

</div>

<style>
@media(max-width:639px){
    .info-grid    { grid-template-columns: 1fr !important }
    .detail-grid  { grid-template-columns: 1fr !important }
    .detail-grid-3{ grid-template-columns: 1fr !important }
}
@media(min-width:640px){
    .detail-grid-3{ grid-template-columns: 1fr 1fr 1fr }
}
</style>

</x-simans-layout>
