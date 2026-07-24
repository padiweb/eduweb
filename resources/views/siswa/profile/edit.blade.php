<x-simans-layout title="Edit Profil">

<div style="max-width:700px">
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
    @php $photo = $detail->photo_path ? Storage::url($detail->photo_path) : null; @endphp
    <div style="position:relative">
        @if($photo)
            <img src="{{ $photo }}" alt="Foto"
                 style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0">
        @else
            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#eff6ff,#dbeafe);display:flex;align-items:center;justify-content:center;border:2px solid #bfdbfe">
                <span style="font-size:24px;font-weight:700;color:#2563eb">{{ strtoupper(substr($user->name,0,2)) }}</span>
            </div>
        @endif
    </div>
    <div style="flex:1">
        <p style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 2px">{{ $user->name }}</p>
        <p style="font-size:12px;color:#64748b;margin:0 0 10px">NIS: {{ $user->nis ?? '-' }}</p>
        <label style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;padding:6px 14px;background:#eff6ff;border:1.5px solid #bfdbfe;color:#2563eb;border-radius:8px">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            Ganti Foto
            <input type="file" name="photo" accept="image/*" style="display:none" onchange="previewPhoto(this)">
        </label>
        <p style="font-size:11px;color:#94a3b8;margin:6px 0 0">JPG, PNG, WebP. Maks. 3MB</p>
    </div>
</div>

<div style="display:grid;gap:16px">

    {{-- Data Pribadi --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(15,23,42,.05)">
        <h2 style="font-size:13px;font-weight:700;color:#0f172a;margin:0 0 16px;display:flex;align-items:center;gap:7px">
            <div style="width:4px;height:16px;background:linear-gradient(180deg,#3b82f6,#2563eb);border-radius:2px"></div>
            Data Pribadi
        </h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px" class="form-grid">
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Nomor HP / WA</label>
                <input type="text" name="phone" value="{{ $user->phone }}"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Nomor WA Siswa</label>
                <input type="text" name="whatsapp" value="{{ $detail->whatsapp }}"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Jenis Kelamin</label>
                <select name="gender" style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none;background:#fff">
                    <option value="">-- Pilih --</option>
                    <option value="L" {{ $detail->gender === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ $detail->gender === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Agama</label>
                <select name="religion" style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none;background:#fff">
                    <option value="">-- Pilih --</option>
                    @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $r)
                        <option value="{{ $r }}" {{ $detail->religion === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Tempat Lahir</label>
                <input type="text" name="birth_place" value="{{ $detail->birth_place }}"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Tanggal Lahir</label>
                <input type="date" name="birth_date" value="{{ $detail->birth_date?->format('Y-m-d') }}"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">NIK (16 digit)</label>
                <input type="text" name="nik" value="{{ $detail->nik }}" maxlength="16" pattern="\d{16}"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none;letter-spacing:1px">
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Nomor Kartu Keluarga</label>
                <input type="text" name="no_kk" value="{{ $detail->no_kk }}" maxlength="16"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none;letter-spacing:1px">
            </div>
        </div>
    </div>

    {{-- Alamat --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(15,23,42,.05)">
        <h2 style="font-size:13px;font-weight:700;color:#0f172a;margin:0 0 14px;display:flex;align-items:center;gap:7px">
            <div style="width:4px;height:16px;background:linear-gradient(180deg,#10b981,#059669);border-radius:2px"></div>
            Alamat
        </h2>
        {{-- Toggle luar negeri --}}
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:14px;padding:10px 14px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0">
            <input type="checkbox" name="is_abroad" id="is-abroad" value="1"
                   {{ $detail->is_abroad ? 'checked' : '' }}
                   style="width:16px;height:16px;accent-color:#3b82f6"
                   onchange="toggleAbroad(this)">
            <span style="font-size:13px;font-weight:600;color:#334155">Beralamat di luar negeri</span>
        </label>

        {{-- Dalam negeri --}}
        <div id="addr-domestic" style="{{ $detail->is_abroad ? 'display:none' : '' }}">
            {{-- Dropdown wilayah via API (id="addr-api") --}}
            <div id="addr-api">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px" class="form-grid">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Provinsi</label>
                        <select id="select-province" name="province" disabled
                                style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none;background:#fff">
                            <option value="">Memuat data provinsi...</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Kabupaten / Kota</label>
                        <select id="select-regency" name="regency" disabled
                                style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none;background:#fff">
                            <option value="">Pilih Provinsi dahulu</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Kecamatan</label>
                        <select id="select-district" name="district" disabled
                                style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none;background:#fff">
                            <option value="">Pilih Kab/Kota dahulu</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Kelurahan / Desa</label>
                        <select id="select-village" name="village" disabled
                                style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none;background:#fff">
                            <option value="">Pilih Kecamatan dahulu</option>
                        </select>
                    </div>
                </div>
            </div>
            {{-- Fallback input teks (jika API tidak tersedia) --}}
            <div id="addr-fallback" style="display:none">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px" class="form-grid">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Provinsi</label>
                        <input type="text" name="province" value="{{ $detail->province }}" placeholder="Nama provinsi..."
                               style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Kabupaten / Kota</label>
                        <input type="text" name="regency" value="{{ $detail->regency }}" placeholder="Kab/Kota..."
                               style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Kecamatan</label>
                        <input type="text" name="district" value="{{ $detail->district }}" placeholder="Kecamatan..."
                               style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Kelurahan / Desa</label>
                        <input type="text" name="village" value="{{ $detail->village }}" placeholder="Kelurahan/Desa..."
                               style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
                    </div>
                </div>
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Nama Jalan / Dusun / RT / RW</label>
                <input type="text" name="street" value="{{ $detail->street }}"
                       placeholder="Contoh: Jl. Merdeka No. 12 RT 02 RW 03 Dusun Suka Maju"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
            </div>
        </div>

        {{-- Luar negeri --}}
        <div id="addr-abroad" style="{{ $detail->is_abroad ? '' : 'display:none' }}">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px" class="form-grid">
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Negara</label>
                    <input type="text" name="country" value="{{ $detail->country }}" placeholder="Nama negara..."
                           style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Alamat Lengkap</label>
                    <input type="text" name="street" value="{{ $detail->street }}" placeholder="Alamat di negara tersebut..."
                           style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
                </div>
            </div>
        </div>
    </div>

    {{-- Data Orang Tua --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(15,23,42,.05)">
        <h2 style="font-size:13px;font-weight:700;color:#0f172a;margin:0 0 16px;display:flex;align-items:center;gap:7px">
            <div style="width:4px;height:16px;background:linear-gradient(180deg,#f59e0b,#d97706);border-radius:2px"></div>
            Data Orang Tua / Wali
        </h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px" class="form-grid">
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Nama Ayah</label>
                <input type="text" name="father_name" value="{{ $detail->father_name }}"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Nama Ibu</label>
                <input type="text" name="mother_name" value="{{ $detail->mother_name }}"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Nomor WA Orang Tua / Wali</label>
                <input type="text" name="parent_whatsapp" value="{{ $detail->parent_whatsapp }}"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
            </div>
        </div>
    </div>

    {{-- Ubah Password --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(15,23,42,.05)">
        <h2 style="font-size:13px;font-weight:700;color:#0f172a;margin:0 0 16px;display:flex;align-items:center;gap:7px">
            <div style="width:4px;height:16px;background:linear-gradient(180deg,#ef4444,#dc2626);border-radius:2px"></div>
            Ubah Password <span style="font-size:11px;font-weight:400;color:#94a3b8">(kosongkan jika tidak ingin mengubah)</span>
        </h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px" class="form-grid">
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Password Baru</label>
                <input type="password" name="password" autocomplete="new-password"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:600;color:#475569;margin-bottom:5px">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" autocomplete="new-password"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;color:#334155;outline:none">
            </div>
        </div>
    </div>
</div>

<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
    <a href="{{ route('siswa.siswa.dashboard') }}"
       style="padding:10px 20px;background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;color:#64748b;text-decoration:none">
        Batal
    </a>
    <button type="submit"
            style="padding:10px 24px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(59,130,246,.3)">
        Simpan Profil
    </button>
</div>
</form>
</div>

<style>
@media(max-width:639px){.form-grid{grid-template-columns:1fr!important}}
</style>

<script>
// Preview foto sebelum upload
function previewPhoto(input) {
    if (!input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var imgs = document.querySelectorAll('img[alt="Foto"]');
        imgs.forEach(function(img) { img.src = e.target.result; });
    };
    reader.readAsDataURL(input.files[0]);
}

// Toggle alamat luar negeri
function toggleAbroad(cb) {
    var dom = document.getElementById('addr-domestic');
    var abr = document.getElementById('addr-abroad');
    if (cb.checked) {
        dom.style.display = 'none';
        abr.style.display = 'block';
    } else {
        dom.style.display = 'block';
        abr.style.display = 'none';
    }
}

// ── WILAYAH INDONESIA - via emsifa API ──────────────────────────────────
// API: https://emsifa.github.io/api-wilayah-indonesia/api/
// Fallback ke input teks biasa jika offline

var API_BASE = 'https://emsifa.github.io/api-wilayah-indonesia/api';

var provSelect   = document.getElementById('select-province');
var regSelect    = document.getElementById('select-regency');
var distSelect   = document.getElementById('select-district');
var villSelect   = document.getElementById('select-village');

function makeOption(val, text, selected) {
    var o = document.createElement('option');
    o.value = text; // simpan nama, bukan ID
    o.dataset.id = val;
    o.textContent = text;
    if (selected) o.selected = true;
    return o;
}

function resetSelect(el, placeholder) {
    el.innerHTML = '';
    el.appendChild(makeOption('', placeholder));
    el.disabled = true;
}

// Load provinsi
fetch(API_BASE + '/provinces.json')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var savedProvince = '{{ $detail->province }}';
        provSelect.disabled = false;
        data.forEach(function(p) {
            provSelect.appendChild(makeOption(p.id, p.name, p.name === savedProvince));
        });
        // Trigger load kabupaten jika provinsi sudah ada
        if (savedProvince) {
            var selectedOpt = provSelect.querySelector('option[value="' + savedProvince + '"]');
            if (selectedOpt) loadRegency(selectedOpt.dataset.id);
        }
    })
    .catch(function() {
        // Jika API gagal, tampilkan input teks
        showFallback();
    });

function loadRegency(provinceId) {
    resetSelect(regSelect, 'Memuat...');
    resetSelect(distSelect, 'Pilih Kecamatan');
    resetSelect(villSelect, 'Pilih Kelurahan/Desa');
    var savedRegency = '{{ $detail->regency }}';
    fetch(API_BASE + '/regencies/' + provinceId + '.json')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            regSelect.disabled = false;
            regSelect.innerHTML = '';
            regSelect.appendChild(makeOption('', 'Pilih Kab/Kota'));
            data.forEach(function(r) {
                regSelect.appendChild(makeOption(r.id, r.name, r.name === savedRegency));
            });
            if (savedRegency) {
                var opt = regSelect.querySelector('option[value="' + savedRegency + '"]');
                if (opt) loadDistrict(opt.dataset.id);
            }
        });
}

function loadDistrict(regencyId) {
    resetSelect(distSelect, 'Memuat...');
    resetSelect(villSelect, 'Pilih Kelurahan/Desa');
    var savedDistrict = '{{ $detail->district }}';
    fetch(API_BASE + '/districts/' + regencyId + '.json')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            distSelect.disabled = false;
            distSelect.innerHTML = '';
            distSelect.appendChild(makeOption('', 'Pilih Kecamatan'));
            data.forEach(function(d) {
                distSelect.appendChild(makeOption(d.id, d.name, d.name === savedDistrict));
            });
            if (savedDistrict) {
                var opt = distSelect.querySelector('option[value="' + savedDistrict + '"]');
                if (opt) loadVillage(opt.dataset.id);
            }
        });
}

function loadVillage(districtId) {
    resetSelect(villSelect, 'Memuat...');
    var savedVillage = '{{ $detail->village }}';
    fetch(API_BASE + '/villages/' + districtId + '.json')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            villSelect.disabled = false;
            villSelect.innerHTML = '';
            villSelect.appendChild(makeOption('', 'Pilih Kelurahan/Desa'));
            data.forEach(function(v) {
                villSelect.appendChild(makeOption(v.id, v.name, v.name === savedVillage));
            });
        });
}

// Event listeners
provSelect.addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    if (opt.dataset.id) loadRegency(opt.dataset.id);
    else { resetSelect(regSelect,'Pilih Kab/Kota'); resetSelect(distSelect,'Pilih Kecamatan'); resetSelect(villSelect,'Pilih Kelurahan/Desa'); }
});
regSelect.addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    if (opt.dataset.id) loadDistrict(opt.dataset.id);
    else { resetSelect(distSelect,'Pilih Kecamatan'); resetSelect(villSelect,'Pilih Kelurahan/Desa'); }
});
distSelect.addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    if (opt.dataset.id) loadVillage(opt.dataset.id);
    else resetSelect(villSelect,'Pilih Kelurahan/Desa');
});

function showFallback() {
    // API tidak tersedia - tampilkan input teks
    document.getElementById('addr-api').style.display = 'none';
    document.getElementById('addr-fallback').style.display = 'block';
}
</script>

</x-simans-layout>
