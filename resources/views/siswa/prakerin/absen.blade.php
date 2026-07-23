<x-simans-layout title="{{ $type === 'check_in' ? 'Absen Masuk' : 'Absen Pulang' }} Prakerin">

<div style="margin-bottom:20px">
    <a href="{{ route('siswa.prakerin.index') }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#64748b;text-decoration:none;margin-bottom:12px">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>
    <h1 style="font-size:20px;font-weight:800;color:#0f172a;margin:0 0 4px">
        {{ $type === 'check_in' ? 'Absen Masuk' : 'Absen Pulang' }} Prakerin
    </h1>
    <p style="font-size:13px;color:#64748b;margin:0">{{ $placement->location->name }}</p>
    @if ($type === 'check_in' && $placement->location->checkin_time)
        <p style="font-size:12px;color:#94a3b8;margin:4px 0 0">
            Jam masuk: {{ $placement->location->checkin_time }}
            @if ($placement->location->checkin_late_after)
                · Toleransi: {{ $placement->location->checkin_late_after }}
            @endif
        </p>
    @endif
</div>

{{-- GPS Status --}}
<div id="gps-box" style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:14px">
    <div id="gps-icon" style="width:34px;height:34px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <svg width="16" height="16" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
    </div>
    <div>
        <p id="gps-text" style="font-size:13px;font-weight:600;color:#64748b;margin:0">Mengambil lokasi GPS...</p>
        <p id="gps-detail" style="font-size:11px;color:#94a3b8;margin:2px 0 0"></p>
    </div>
</div>

{{-- Kamera --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;margin-bottom:12px;box-shadow:0 1px 4px rgba(15,23,42,0.06)">
    <div style="position:relative">
        <video id="video" autoplay playsinline
               style="width:100%;aspect-ratio:3/4;object-fit:cover;background:#0f172a;display:block"></video>
        <canvas id="canvas" style="display:none"></canvas>
        <img id="preview" style="display:none;width:100%;aspect-ratio:3/4;object-fit:cover" alt="Foto selfie"/>
        <div id="guide-overlay" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none">
            <div style="width:180px;height:180px;border-radius:50%;border:2px dashed rgba(255,255,255,0.5)"></div>
        </div>
        <button id="btn-retake" onclick="retakePhoto()"
                style="display:none;position:absolute;top:12px;right:12px;padding:6px 12px;background:rgba(255,255,255,0.9);color:#334155;font-size:12px;font-weight:600;border-radius:8px;border:1px solid #e2e8f0;cursor:pointer">
            ↩ Ulang
        </button>
    </div>

    <div style="padding:16px;display:flex;flex-direction:column;gap:10px">

        {{-- Tombol Ambil Foto --}}
        <button id="btn-capture" onclick="capturePhoto()"
                style="width:100%;padding:14px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;font-weight:700;border-radius:12px;border:none;cursor:pointer;font-size:15px;box-shadow:0 3px 10px rgba(59,130,246,0.35);display:flex;align-items:center;justify-content:center;gap:8px">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Ambil Foto Selfie
        </button>

        {{-- Tombol Kirim — SELALU tersembunyi via JS, bukan CSS class --}}
        <button id="btn-submit" onclick="submitAbsen()"
                style="width:100%;padding:14px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-weight:700;border-radius:12px;border:none;cursor:pointer;font-size:15px;box-shadow:0 3px 10px rgba(16,185,129,0.35);align-items:center;justify-content:center;gap:8px;display:none">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span id="btn-submit-text">Kirim Absensi</span>
        </button>

        {{-- Hint GPS belum siap --}}
        <p id="gps-hint" style="display:none;text-align:center;font-size:12px;color:#f59e0b;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px;margin:0">
            ⏳ Menunggu sinyal GPS... Pastikan lokasi aktif di HP.
        </p>
    </div>
</div>

@if ($placement->location->latitude && $placement->location->longitude)
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:12px 16px;text-align:center;margin-bottom:12px">
        <p style="font-size:12px;color:#64748b;margin:0">
            📍 Radius check-in: <strong style="color:#334155">{{ $placement->location->radius_meters ?? 300 }}m</strong>
            dari {{ $placement->location->name }}
        </p>
    </div>
@endif

<div id="alert" style="display:none;margin-top:14px;padding:12px 16px;border-radius:12px;font-size:13.5px;font-weight:500;border:1px solid"></div>

<script>
const TYPE     = '{{ $type }}';
const CSRF     = '{{ csrf_token() }}';
const STORE_URL= '{{ route("siswa.prakerin.absen.store") }}';
const BACK_URL = '{{ route("siswa.prakerin.index") }}';

let photoData = null;
let gpsLat    = null;
let gpsLng    = null;
let gpsAccuracy = null;
let stream    = null;

// ── GPS ──────────────────────────────────────────────────────────────────
function initGps() {
    if (!navigator.geolocation) {
        setGpsStatus('error', 'GPS tidak didukung perangkat ini', '');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        function(pos) {
            gpsLat      = pos.coords.latitude;
            gpsLng      = pos.coords.longitude;
            gpsAccuracy = pos.coords.accuracy;
            setGpsStatus('ok', 'Lokasi ditemukan', 'Akurasi ±' + Math.round(gpsAccuracy) + 'm');
            updateUI();
        },
        function(err) {
            setGpsStatus('error', 'Gagal mengambil lokasi GPS', 'Aktifkan izin lokasi di pengaturan HP');
            updateUI();
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
}

function setGpsStatus(status, text, detail) {
    document.getElementById('gps-text').textContent   = text;
    document.getElementById('gps-detail').textContent = detail;
    const icon = document.getElementById('gps-icon');
    if (status === 'ok') {
        icon.style.background = '#ecfdf5';
        icon.style.border     = '1px solid #bbf7d0';
        icon.innerHTML = '<svg width="16" height="16" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
        document.getElementById('gps-text').style.color = '#059669';
    } else {
        icon.style.background = '#fff1f2';
        icon.style.border     = '1px solid #fecaca';
        icon.innerHTML = '<svg width="16" height="16" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
        document.getElementById('gps-text').style.color = '#dc2626';
    }
}

// ── KAMERA ───────────────────────────────────────────────────────────────
async function initCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 960 } },
            audio: false
        });
        document.getElementById('video').srcObject = stream;
    } catch(e) {
        showAlert('error', 'Gagal akses kamera: ' + e.message + '. Pastikan izin kamera sudah diberikan.');
    }
}

function capturePhoto() {
    const v = document.getElementById('video');
    const c = document.getElementById('canvas');
    c.width  = v.videoWidth  || 720;
    c.height = v.videoHeight || 960;
    c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
    photoData = c.toDataURL('image/jpeg', 0.82);

    document.getElementById('preview').src     = photoData;
    document.getElementById('preview').style.display   = 'block';
    document.getElementById('video').style.display     = 'none';
    document.getElementById('guide-overlay').style.display = 'none';
    document.getElementById('btn-capture').style.display   = 'none';
    document.getElementById('btn-retake').style.display    = 'block';

    if (stream) stream.getTracks().forEach(function(t) { t.stop(); });
    updateUI();
}

function retakePhoto() {
    photoData = null;
    document.getElementById('preview').style.display   = 'none';
    document.getElementById('video').style.display     = 'block';
    document.getElementById('guide-overlay').style.display = 'flex';
    document.getElementById('btn-capture').style.display   = 'flex';
    document.getElementById('btn-retake').style.display    = 'none';
    updateUI();
    initCamera();
}

// ── UPDATE UI STATE ───────────────────────────────────────────────────────
function updateUI() {
    var btnSubmit = document.getElementById('btn-submit');
    var gpsHint   = document.getElementById('gps-hint');

    if (photoData && gpsLat !== null) {
        // Foto ada + GPS ada → tampilkan tombol kirim
        btnSubmit.style.display = 'flex';
        gpsHint.style.display   = 'none';
    } else if (photoData && gpsLat === null) {
        // Foto ada tapi GPS belum → tampilkan hint
        btnSubmit.style.display = 'none';
        gpsHint.style.display   = 'block';
    } else {
        // Belum ada foto → sembunyikan keduanya
        btnSubmit.style.display = 'none';
        gpsHint.style.display   = 'none';
    }
}

// ── KIRIM ─────────────────────────────────────────────────────────────────
async function submitAbsen() {
    if (!photoData || gpsLat === null) {
        showAlert('error', 'Foto selfie dan GPS diperlukan untuk absensi.');
        return;
    }

    var btn  = document.getElementById('btn-submit');
    var text = document.getElementById('btn-submit-text');
    btn.disabled        = true;
    btn.style.opacity   = '0.7';
    text.textContent    = 'Mengirim...';

    try {
        var res = await fetch(STORE_URL, {
            method : 'POST',
            headers: {
                'Content-Type'  : 'application/json',
                'X-CSRF-TOKEN'  : CSRF,
                'Accept'        : 'application/json'
            },
            body: JSON.stringify({
                type      : TYPE,
                selfie    : photoData,
                latitude  : gpsLat,
                longitude : gpsLng,
                accuracy  : gpsAccuracy
            })
        });

        var data = await res.json();

        if (data.success) {
            showAlert(data.warning ? 'warning' : 'success', data.message);
            setTimeout(function() { window.location.href = BACK_URL; }, 2200);
        } else {
            showAlert('error', data.message || 'Gagal mengirim absensi.');
            btn.disabled      = false;
            btn.style.opacity = '1';
            text.textContent  = 'Kirim Absensi';
        }
    } catch(e) {
        showAlert('error', 'Error koneksi: ' + e.message);
        btn.disabled      = false;
        btn.style.opacity = '1';
        text.textContent  = 'Kirim Absensi';
    }
}

function showAlert(type, msg) {
    var el = document.getElementById('alert');
    el.style.display = 'block';
    if (type === 'success') {
        el.style.background   = '#f0fdf4';
        el.style.color        = '#15803d';
        el.style.borderColor  = '#bbf7d0';
    } else if (type === 'warning') {
        el.style.background   = '#fffbeb';
        el.style.color        = '#b45309';
        el.style.borderColor  = '#fde68a';
    } else {
        el.style.background   = '#fff1f2';
        el.style.color        = '#dc2626';
        el.style.borderColor  = '#fecaca';
    }
    el.textContent = msg;
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ── INIT ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    initGps();
    initCamera();
    updateUI(); // pastikan state awal benar
});
</script>
</x-simans-layout>
