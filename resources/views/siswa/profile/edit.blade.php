<x-simans-layout title="Edit Profil">

<div style="max-width:680px;margin:0 auto">

<div style="margin-bottom:24px">
    <h1 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 4px">Profil Saya</h1>
    <p style="font-size:13px;color:#64748b;margin:0">Lengkapi biodata untuk keperluan administrasi sekolah</p>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:12px;font-size:13px;font-weight:500;margin-bottom:20px;display:flex;align-items:center;gap:8px">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div style="background:#fff1f2;border:1px solid #fecaca;color:#dc2626;padding:12px 16px;border-radius:12px;font-size:13px;margin-bottom:20px">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif

<form method="POST" action="{{ route('siswa.profile.update') }}" enctype="multipart/form-data">
@csrf

{{-- Foto profil --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;margin-bottom:16px;display:flex;align-items:center;gap:20px;box-shadow:0 1px 3px rgba(15,23,42,.05)">
    <div style="flex-shrink:0">
        @php $photo = $detail->photo_path ? Storage::url($detail->photo_path) : null; @endphp
        <div id="photo-wrap" style="width:80px;height:80px;border-radius:50%;overflow:hidden;border:2.5px solid #e2e8f0">
            @if($photo)
                <img id="photo-img" src="{{ $photo }}" style="width:100%;height:100%;object-fit:cover">
            @else
                <div id="photo-initial" style="width:100%;height:100%;background:linear-gradient(135deg,#eff6ff,#dbeafe);display:flex;align-items:center;justify-content:center">
                    <span style="font-size:22px;font-weight:700;color:#2563eb">{{ strtoupper(substr($user->name,0,2)) }}</span>
                </div>
            @endif
        </div>
    </div>
    <div style="flex:1;min-width:0">
        <p style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 2px">{{ $user->name }}</p>
        <p style="font-size:12px;color:#64748b;margin:0 0 12px">{{ $user->email }}</p>
        <label style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;padding:6px 14px;background:#eff6ff;border:1.5px solid #bfdbfe;color:#2563eb;border-radius:8px">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            Ganti Foto
            <input type="file" name="photo" accept="image/*" style="display:none" onchange="previewPhoto(this)">
        </label>
        <p style="font-size:11px;color:#94a3b8;margin:6px 0 0">JPG/PNG/WebP, maks 3MB</p>
    </div>
</div>

<div style="display:flex;flex-direction:column;gap:16px">

{{-- Data Pribadi --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(15,23,42,.05)">
    <h2 class="sec-title">
        <div class="sec-bar" style="background:linear-gradient(180deg,#3b82f6,#2563eb)"></div>
        Data Pribadi
    </h2>
    <div class="form-grid">
        <div>
            <label class="lbl">Nomor HP / WA</label>
            <input type="text" name="phone" value="{{ $user->phone }}" class="inp" placeholder="08xxxxxxxxxx">
        </div>
        <div>
            <label class="lbl">WA Siswa (jika beda dengan HP)</label>
            <input type="text" name="whatsapp" value="{{ $detail->whatsapp }}" class="inp" placeholder="08xxxxxxxxxx">
        </div>
        <div>
            <label class="lbl">Jenis Kelamin</label>
            <select name="gender" class="inp">
                <option value="">-- Pilih --</option>
                <option value="L" {{ $detail->gender==='L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ $detail->gender==='P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>
        <div>
            <label class="lbl">Agama</label>
            <select name="religion" class="inp">
                <option value="">-- Pilih --</option>
                @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $r)
                    <option value="{{ $r }}" {{ $detail->religion===$r ? 'selected' : '' }}>{{ $r }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="lbl">Tempat Lahir</label>
            <input type="text" name="birth_place" value="{{ $detail->birth_place }}" class="inp">
        </div>
        <div>
            <label class="lbl">Tanggal Lahir</label>
            <input type="date" name="birth_date" value="{{ $detail->birth_date?->format('Y-m-d') }}" class="inp">
        </div>
        <div>
            <label class="lbl">NIK (16 digit)</label>
            <input type="text" name="nik" value="{{ $detail->nik }}" maxlength="16" class="inp" placeholder="3300xxxxxxxxxx">
        </div>
        <div>
            <label class="lbl">Nomor Kartu Keluarga</label>
            <input type="text" name="no_kk" value="{{ $detail->no_kk }}" maxlength="16" class="inp">
        </div>
    </div>
</div>

{{-- Alamat --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(15,23,42,.05)">
    <h2 class="sec-title">
        <div class="sec-bar" style="background:linear-gradient(180deg,#10b981,#059669)"></div>
        Alamat
    </h2>

    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:16px;padding:10px 14px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0">
        <input type="checkbox" name="is_abroad" id="chk-abroad" value="1"
               {{ $detail->is_abroad ? 'checked' : '' }}
               style="width:16px;height:16px;accent-color:#3b82f6"
               onchange="toggleAbroad(this)">
        <span style="font-size:13px;font-weight:600;color:#334155">Beralamat di luar negeri</span>
    </label>

    {{-- DALAM NEGERI --}}
    <div id="addr-dn" style="{{ $detail->is_abroad ? 'display:none' : '' }}">

        {{-- Provinsi --}}
        <div style="margin-bottom:12px">
            <label class="lbl">Provinsi <span style="color:#ef4444">*</span></label>
            <div style="position:relative">
                <select id="sel-prov" name="province" class="inp"
                        style="padding-right:36px">
                    <option value="" data-id="">Memuat provinsi...</option>
                </select>
                <div id="spin-prov" class="spin-wrap">
                    <svg class="spin-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Kab/Kota + Kecamatan --}}
        <div class="form-grid" style="margin-bottom:12px">
            <div>
                <label class="lbl">Kabupaten / Kota <span style="color:#ef4444">*</span></label>
                <div style="position:relative">
                    <select id="sel-reg" name="regency" class="inp" style="padding-right:36px" disabled>
                        <option value="" data-id="">Pilih Provinsi dahulu</option>
                    </select>
                    <div id="spin-reg" class="spin-wrap" style="display:none">
                        <svg class="spin-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div>
                <label class="lbl">Kecamatan <span style="color:#ef4444">*</span></label>
                <div style="position:relative">
                    <select id="sel-dist" name="district" class="inp" style="padding-right:36px" disabled>
                        <option value="" data-id="">Pilih Kab/Kota dahulu</option>
                    </select>
                    <div id="spin-dist" class="spin-wrap" style="display:none">
                        <svg class="spin-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kelurahan/Desa --}}
        <div style="margin-bottom:12px">
            <label class="lbl">Kelurahan / Desa <span style="color:#ef4444">*</span></label>
            <div style="position:relative">
                <select id="sel-vill" name="village" class="inp" style="padding-right:36px" disabled>
                    <option value="" data-id="">Pilih Kecamatan dahulu</option>
                </select>
                <div id="spin-vill" class="spin-wrap" style="display:none">
                    <svg class="spin-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Dusun/RT manual --}}
        <div>
            <label class="lbl">Nama Jalan / Dusun / RT / RW <span style="font-weight:400;color:#94a3b8">(tulis manual)</span></label>
            <input type="text" name="street" value="{{ $detail->street }}" class="inp"
                   placeholder="Contoh: Jl. Merdeka No. 12 RT 02 RW 03 Dusun Suka Maju">
        </div>

        <div id="addr-error" style="display:none;margin-top:10px;padding:10px 14px;background:#fff1f2;border:1px solid #fecaca;border-radius:8px;font-size:12px;color:#dc2626">
            Gagal memuat data wilayah. Silakan isi alamat secara manual di bawah.
            <button type="button" onclick="showManualInput()" style="font-size:12px;font-weight:600;color:#2563eb;background:none;border:none;cursor:pointer;margin-left:8px;text-decoration:underline">Isi Manual</button>
        </div>
    </div>

    {{-- LUAR NEGERI --}}
    <div id="addr-ln" style="{{ $detail->is_abroad ? '' : 'display:none' }}">
        <div class="form-grid">
            <div>
                <label class="lbl">Negara</label>
                <input type="text" name="country" value="{{ $detail->country }}" class="inp" placeholder="Nama negara">
            </div>
            <div>
                <label class="lbl">Alamat Lengkap</label>
                <input type="text" name="street" value="{{ $detail->street }}" class="inp" placeholder="Alamat di negara tersebut">
            </div>
        </div>
    </div>
</div>

{{-- Orang Tua --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(15,23,42,.05)">
    <h2 class="sec-title">
        <div class="sec-bar" style="background:linear-gradient(180deg,#f59e0b,#d97706)"></div>
        Data Orang Tua / Wali
    </h2>
    <div class="form-grid">
        <div>
            <label class="lbl">Nama Ayah</label>
            <input type="text" name="father_name" value="{{ $detail->father_name }}" class="inp">
        </div>
        <div>
            <label class="lbl">Nama Ibu</label>
            <input type="text" name="mother_name" value="{{ $detail->mother_name }}" class="inp">
        </div>
        <div style="grid-column:1/-1">
            <label class="lbl">Nomor WA Orang Tua / Wali</label>
            <input type="text" name="parent_whatsapp" value="{{ $detail->parent_whatsapp }}" class="inp">
        </div>
    </div>
</div>

{{-- Password --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(15,23,42,.05)">
    <h2 class="sec-title">
        <div class="sec-bar" style="background:linear-gradient(180deg,#ef4444,#dc2626)"></div>
        Ubah Password
        <span style="font-size:11px;font-weight:400;color:#94a3b8;margin-left:6px">— kosongkan jika tidak ingin mengubah</span>
    </h2>
    <div class="form-grid">
        <div>
            <label class="lbl">Password Baru</label>
            <input type="password" name="password" autocomplete="new-password" class="inp">
        </div>
        <div>
            <label class="lbl">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" autocomplete="new-password" class="inp">
        </div>
    </div>
</div>

</div>

<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-bottom:40px">
    <a href="{{ route('siswa.siswa.dashboard') }}"
       style="padding:10px 20px;background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;color:#64748b;text-decoration:none">
        Batal
    </a>
    <button type="submit"
            style="padding:10px 28px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(59,130,246,.3)">
        Simpan Profil
    </button>
</div>
</form>
</div>

<style>
.lbl       { display:block; font-size:11.5px; font-weight:600; color:#475569; margin-bottom:5px }
.inp       { width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:9px 12px; font-size:13px;
             color:#334155; outline:none; background:#fff; transition:border-color .15s; box-sizing:border-box }
.inp:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.08) }
.inp:disabled { background:#f1f5f9; color:#94a3b8; cursor:not-allowed }
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px }
.sec-title { font-size:13px; font-weight:700; color:#0f172a; margin:0 0 16px;
             display:flex; align-items:center; gap:7px }
.sec-bar   { width:4px; height:16px; border-radius:2px; flex-shrink:0 }

/* Spinner inside select */
.spin-wrap { position:absolute; right:10px; top:50%; transform:translateY(-50%); pointer-events:none }
.spin-icon { width:16px; height:16px; animation:spin .8s linear infinite }
@keyframes spin { to { transform:rotate(360deg) } }

@media(max-width:639px) { .form-grid { grid-template-columns:1fr !important } }
</style>

<script>
// ─── Preview foto ─────────────────────────────────────────────────────────
function previewPhoto(input) {
    if (!input.files[0]) return;
    var r = new FileReader();
    r.onload = function(e) {
        var wrap = document.getElementById('photo-wrap');
        wrap.innerHTML = '<img style="width:100%;height:100%;object-fit:cover" src="' + e.target.result + '">';
    };
    r.readAsDataURL(input.files[0]);
}

// ─── Toggle luar negeri ───────────────────────────────────────────────────
function toggleAbroad(cb) {
    document.getElementById('addr-dn').style.display = cb.checked ? 'none' : 'block';
    document.getElementById('addr-ln').style.display = cb.checked ? 'block' : 'none';
}

// ─── Dropdown Wilayah ─────────────────────────────────────────────────────
//
// PRINSIP:
// - "initial" values: nilai yang SUDAH TERSIMPAN di DB (untuk auto-select saat halaman buka)
// - Setelah user memilih manual, initial values TIDAK dipakai lagi
// - Route: /api/wilayah/* (Laravel controller, bukan API eksternal)
//
var ROUTE_PROV  = '{{ route("wilayah.provinces") }}';
var ROUTE_REG   = '{{ url("api/wilayah/regencies") }}';
var ROUTE_DIST  = '{{ url("api/wilayah/districts") }}';
var ROUTE_VILL  = '{{ url("api/wilayah/villages") }}';

// Nilai tersimpan — hanya untuk auto-load pertama kali
var INIT = {
    prov : @json($detail->province ?? ''),
    reg  : @json($detail->regency  ?? ''),
    dist : @json($detail->district ?? ''),
    vill : @json($detail->village  ?? ''),
};
// Flag: apakah ini sedang auto-load (bukan pilihan user)?
var autoLoading = true;

var selProv = document.getElementById('sel-prov');
var selReg  = document.getElementById('sel-reg');
var selDist = document.getElementById('sel-dist');
var selVill = document.getElementById('sel-vill');

// ─── Helper: isi select dari array data ──────────────────────────────────
// Kembalikan ID dari item yang ter-select (untuk cascade)
function fillSelect(sel, data, nameKey, idKey, matchName) {
    sel.innerHTML = '';
    var defOpt = document.createElement('option');
    defOpt.value    = '';
    defOpt.dataset.id = '';
    defOpt.textContent = '-- Pilih --';
    sel.appendChild(defOpt);

    var matchedId = '';
    data.forEach(function(item) {
        var opt           = document.createElement('option');
        opt.value         = item[nameKey];   // VALUE = nama (yang disimpan ke DB)
        opt.dataset.id    = item[idKey];     // data-id = ID wilayah (untuk cascade)
        opt.textContent   = item[nameKey];
        if (item[nameKey] === matchName) {
            opt.selected = true;
            matchedId    = item[idKey];
        }
        sel.appendChild(opt);
    });

    sel.disabled = false;
    return matchedId;
}

// ─── Spinner ─────────────────────────────────────────────────────────────
function showSpin(id, show) {
    var el = document.getElementById(id);
    if (el) el.style.display = show ? 'block' : 'none';
}

// ─── Reset select ke bawah ───────────────────────────────────────────────
function resetFrom(level) {
    if (level <= 1) {
        selReg.innerHTML  = '<option value="" data-id="">-- Pilih --</option>';
        selReg.disabled   = true;
    }
    if (level <= 2) {
        selDist.innerHTML = '<option value="" data-id="">-- Pilih --</option>';
        selDist.disabled  = true;
    }
    if (level <= 3) {
        selVill.innerHTML = '<option value="" data-id="">-- Pilih --</option>';
        selVill.disabled  = true;
    }
}

// ─── Fetch dengan error handling ─────────────────────────────────────────
function fetchWilayah(url, onSuccess, onError) {
    fetch(url)
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(onSuccess)
        .catch(onError || function(e) {
            console.error('Gagal load wilayah:', e);
            document.getElementById('addr-error').style.display = 'block';
        });
}

// ─── LOAD PROVINSI (saat halaman load) ───────────────────────────────────
showSpin('spin-prov', true);
fetchWilayah(ROUTE_PROV, function(data) {
    showSpin('spin-prov', false);
    var matchedId = fillSelect(selProv, data, 'name', 'id', INIT.prov);

    if (matchedId && INIT.prov) {
        // Auto-load kabupaten untuk provinsi yang tersimpan
        loadReg(matchedId, true);
    }
    // Setelah provinsi load, aktifkan event listener user
    selProv.addEventListener('change', onProvChange);

}, function() {
    showSpin('spin-prov', false);
    document.getElementById('addr-error').style.display = 'block';
});

// ─── EVENT: User ganti provinsi ──────────────────────────────────────────
function onProvChange() {
    var opt = this.options[this.selectedIndex];
    var id  = opt ? opt.dataset.id : '';
    resetFrom(1);
    document.getElementById('addr-error').style.display = 'none';
    if (id) loadReg(id, false); // false = bukan auto-load, jangan auto-select
}

// ─── Load Kabupaten/Kota ─────────────────────────────────────────────────
function loadReg(provId, isAuto) {
    showSpin('spin-reg', true);
    selReg.disabled = true;

    // Tentukan nilai yang di-match: hanya saat auto-load awal
    var matchVal = (isAuto && autoLoading) ? INIT.reg : '';

    fetchWilayah(ROUTE_REG + '/' + provId, function(data) {
        showSpin('spin-reg', false);
        var matchedId = fillSelect(selReg, data, 'name', 'id', matchVal);

        if (matchedId && isAuto && autoLoading && INIT.dist) {
            loadDist(matchedId, true);
        }
        // Aktifkan listener hanya sekali
        selReg.removeEventListener('change', onRegChange);
        selReg.addEventListener('change', onRegChange);
    });
}

// ─── EVENT: User ganti kabupaten ─────────────────────────────────────────
function onRegChange() {
    var opt = this.options[this.selectedIndex];
    var id  = opt ? opt.dataset.id : '';
    resetFrom(2);
    if (id) loadDist(id, false);
}

// ─── Load Kecamatan ──────────────────────────────────────────────────────
function loadDist(regId, isAuto) {
    showSpin('spin-dist', true);
    selDist.disabled = true;

    var matchVal = (isAuto && autoLoading) ? INIT.dist : '';

    fetchWilayah(ROUTE_DIST + '/' + regId, function(data) {
        showSpin('spin-dist', false);
        var matchedId = fillSelect(selDist, data, 'name', 'id', matchVal);

        if (matchedId && isAuto && autoLoading && INIT.vill) {
            loadVill(matchedId, true);
        }
        selDist.removeEventListener('change', onDistChange);
        selDist.addEventListener('change', onDistChange);
    });
}

// ─── EVENT: User ganti kecamatan ─────────────────────────────────────────
function onDistChange() {
    var opt = this.options[this.selectedIndex];
    var id  = opt ? opt.dataset.id : '';
    resetFrom(3);
    if (id) loadVill(id, false);
}

// ─── Load Kelurahan/Desa ─────────────────────────────────────────────────
function loadVill(distId, isAuto) {
    showSpin('spin-vill', true);
    selVill.disabled = true;

    var matchVal = (isAuto && autoLoading) ? INIT.vill : '';

    fetchWilayah(ROUTE_VILL + '/' + distId, function(data) {
        showSpin('spin-vill', false);
        fillSelect(selVill, data, 'name', 'id', matchVal);

        // Selesai auto-load — mulai sekarang pilihan user tidak pakai initial values
        if (isAuto) autoLoading = false;
    });
}

// ─── Manual input fallback ────────────────────────────────────────────────
function showManualInput() {
    // Ganti semua select ke input teks
    ['sel-prov','sel-reg','sel-dist','sel-vill'].forEach(function(id, i) {
        var sel    = document.getElementById(id);
        var names  = ['province','regency','district','village'];
        var inits  = [INIT.prov, INIT.reg, INIT.dist, INIT.vill];
        var inp    = document.createElement('input');
        inp.type   = 'text';
        inp.name   = names[i];
        inp.value  = inits[i];
        inp.className = 'inp';
        inp.placeholder = sel.placeholder || '';
        sel.parentNode.replaceChild(inp, sel);
    });
    document.getElementById('addr-error').style.display = 'none';
}
</script>

</x-simans-layout>
