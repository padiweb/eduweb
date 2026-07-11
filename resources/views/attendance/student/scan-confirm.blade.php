<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi — EduWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-sm">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-2xl bg-emerald-500 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
            </svg>
        </div>
        <p class="font-bold text-white">EduWeb</p>
        <p class="text-gray-500 text-xs">{{ auth()->user()->school->name }}</p>
    </div>

    {{-- State: Loading GPS --}}
    <div id="state-loading" class="text-center">
        <div class="w-16 h-16 rounded-full bg-blue-500/10 border border-blue-500/20 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-400 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-white mb-2">Mendeteksi Lokasi</h2>
        <p class="text-gray-400 text-sm">Pastikan GPS aktif di HP kamu</p>
    </div>

    {{-- State: GPS Error --}}
    <div id="state-gps-error" class="text-center hidden">
        <div class="w-16 h-16 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-white mb-2">GPS Tidak Aktif</h2>
        <p class="text-gray-400 text-sm mb-4" id="gps-error-msg">Izinkan akses lokasi di browser kamu</p>
        <button onclick="requestGPS()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-3 rounded-2xl transition-colors text-sm">
            Coba Lagi
        </button>
    </div>

    {{-- State: Siap --}}
    <div id="state-ready" class="hidden">
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-5 mb-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-emerald-900 border border-emerald-700/50 flex items-center justify-center text-sm font-bold text-emerald-400">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div>
                    <p class="font-semibold text-white text-sm">{{ auth()->user()->name }}</p>
                    <p class="text-gray-400 text-xs">NIS: {{ auth()->user()->nis }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-3 py-2">
                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                <span class="text-emerald-400 text-xs font-medium">Lokasi terdeteksi</span>
                <span class="text-emerald-600 text-xs ml-auto" id="gps-accuracy"></span>
            </div>
        </div>

        <button id="btn-absen" class="w-full bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white font-bold py-4 rounded-2xl transition-all text-base">
            Absen Sekarang
        </button>
        <p class="text-center text-gray-600 text-xs mt-3">{{ now()->format('H:i') }} WIB</p>
    </div>

    {{-- State: Submitting --}}
    <div id="state-submitting" class="text-center hidden">
        <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-emerald-400 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
            </svg>
        </div>
        <p class="text-gray-400 text-sm">Menyimpan absensi...</p>
    </div>

    {{-- State: Berhasil --}}
    <div id="state-success" class="text-center hidden">
        <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-white mb-1" id="success-title">Absensi Berhasil!</h2>
        <p class="text-gray-400 text-sm mb-6" id="success-detail"></p>
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-4 text-left mb-4">
            <div class="flex justify-between text-sm py-2">
                <span class="text-gray-400">Nama</span>
                <span class="text-white font-medium">{{ auth()->user()->name }}</span>
            </div>
            <div class="flex justify-between text-sm py-2 border-t border-white/5">
                <span class="text-gray-400">Status</span>
                <span class="font-semibold" id="success-status"></span>
            </div>
            <div class="flex justify-between text-sm py-2 border-t border-white/5">
                <span class="text-gray-400">Waktu</span>
                <span class="text-white" id="success-time"></span>
            </div>
        </div>
        <a href="{{ route('siswa.attendance.history') }}" class="block w-full text-center text-emerald-400 hover:text-emerald-300 text-sm font-medium transition-colors py-2">
            Lihat Riwayat Absensi →
        </a>
    </div>

    {{-- State: Gagal --}}
    <div id="state-error" class="text-center hidden">
        <div class="w-16 h-16 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-white mb-2">Absensi Gagal</h2>
        <p class="text-gray-400 text-sm mb-6" id="error-msg"></p>
        <button onclick="showState('ready')" class="w-full bg-gray-800 hover:bg-gray-700 text-gray-300 font-medium py-3 rounded-2xl border border-white/10 transition-colors text-sm">
            Coba Lagi
        </button>
    </div>

</div>

<script>
const TOKEN = @json($token);
const CSRF  = document.querySelector('meta[name="csrf-token"]').content;
let gps     = null;

const states = ['loading', 'gps-error', 'ready', 'submitting', 'success', 'error'];

function showState(name) {
    states.forEach(s => {
        document.getElementById('state-' + s)?.classList.toggle('hidden', s !== name);
    });
}

function requestGPS() {
    showState('loading');
    if (!navigator.geolocation) {
        document.getElementById('gps-error-msg').textContent = 'Browser tidak mendukung GPS.';
        showState('gps-error');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        pos => {
            gps = { lat: pos.coords.latitude, lng: pos.coords.longitude, acc: pos.coords.accuracy };
            document.getElementById('gps-accuracy').textContent = '±' + Math.round(gps.acc) + 'm';
            showState('ready');
        },
        err => {
            const msgs = { 1: 'Izinkan akses lokasi di pengaturan browser.', 2: 'Posisi tidak tersedia. Pastikan GPS aktif.', 3: 'GPS timeout. Coba di area terbuka.' };
            document.getElementById('gps-error-msg').textContent = msgs[err.code] || 'Gagal mendeteksi lokasi.';
            showState('gps-error');
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
}

document.getElementById('btn-absen')?.addEventListener('click', async function() {
    if (!gps) { requestGPS(); return; }
    showState('submitting');

    try {
        const res  = await fetch('/siswa/absensi/submit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ token: TOKEN, latitude: gps.lat, longitude: gps.lng, gps_accuracy: gps.acc }),
        });
        const data = await res.json();

        if (data.success) {
            document.getElementById('success-title').textContent  = 'Absensi ' + data.status_label + '!';
            document.getElementById('success-status').textContent = data.status_label;
            document.getElementById('success-status').className   = data.status === 'terlambat' ? 'font-semibold text-amber-400' : 'font-semibold text-emerald-400';
            document.getElementById('success-time').textContent   = data.scanned_at + ' WIB';
            showState('success');
        } else {
            document.getElementById('error-msg').textContent = data.message;
            showState('error');
        }
    } catch(e) {
        document.getElementById('error-msg').textContent = 'Koneksi gagal. Periksa internet kamu.';
        showState('error');
    }
});

requestGPS();
</script>
</body>
</html>