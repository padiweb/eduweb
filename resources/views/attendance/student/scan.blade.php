<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi — EduWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-sm mx-auto">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center mx-auto mb-3 shadow-lg shadow-emerald-500/20">
            <svg class="w-7 h-7 text-gray-900" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
            </svg>
        </div>
        <h1 class="font-bold text-gray-900">EduWeb</h1>
        <p class="text-gray-400 text-xs mt-0.5">{{ auth()->user()->school->name }}</p>
    </div>

    {{-- ── State: Loading GPS ── --}}
    <div id="state-loading" class="text-center">
        <div class="w-20 h-20 rounded-full bg-blue-500/10 border-2 border-blue-500/20 flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Mendeteksi Lokasi</h2>
        <p class="text-gray-500 text-sm">Pastikan GPS aktif di HP kamu</p>
        <div class="flex justify-center mt-4 gap-1">
            <span class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay:0s"></span>
            <span class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay:.15s"></span>
            <span class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay:.3s"></span>
        </div>
    </div>

    {{-- ── State: GPS Error ── --}}
    <div id="state-gps-error" class="text-center hidden">
        <div class="w-20 h-20 rounded-full bg-red-500/10 border-2 border-red-500/20 flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">GPS Tidak Aktif</h2>
        <p class="text-gray-500 text-sm mb-6" id="gps-error-msg">Izinkan akses lokasi di pengaturan browser</p>
        <button onclick="requestGPS()"
                class="w-full bg-blue-600 hover:bg-blue-700 text-gray-900 font-semibold py-3.5 rounded-2xl transition-colors">
            Coba Lagi
        </button>
    </div>

    {{-- ── State: Siap Absen ── --}}
    <div id="state-ready" class="hidden">
        {{-- Info siswa --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-4 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-emerald-900 border border-emerald-700/50 flex items-center justify-center text-base font-bold text-blue-600 flex-shrink-0">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-gray-500 text-xs">NIS: {{ auth()->user()->nis }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-3 bg-blue-600/10 border border-blue-200 rounded-xl px-3 py-2">
                <svg class="w-3.5 h-3.5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                <span class="text-blue-600 text-xs font-medium">Lokasi terverifikasi</span>
                <span class="text-emerald-600 text-xs ml-auto" id="gps-accuracy-text"></span>
            </div>
        </div>

        {{-- Tombol absen --}}
        <button id="btn-absen"
                class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-gray-900 font-bold py-4 rounded-2xl transition-all text-lg shadow-lg shadow-emerald-500/20">
            ✓ Absen Sekarang
        </button>

        <p class="text-center text-gray-400 text-xs mt-3">{{ now()->format('H:i') }} WIB · {{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- ── State: Submitting ── --}}
    <div id="state-submitting" class="text-center hidden">
        <div class="w-20 h-20 rounded-full bg-blue-600/10 border-2 border-blue-200 flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-blue-600 animate-spin" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
            </svg>
        </div>
        <p class="text-gray-500">Menyimpan absensi...</p>
    </div>

    {{-- ── State: Berhasil ── --}}
    <div id="state-success" class="text-center hidden">
        <div class="w-20 h-20 rounded-full bg-blue-600/10 border-2 border-blue-200 flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-1" id="success-title">Absensi Berhasil!</h2>
        <p class="text-sm mb-6" id="success-subtitle"></p>

        <div class="bg-white border border-gray-200 rounded-2xl divide-y divide-gray-100 text-left mb-5">
            <div class="flex justify-between items-center px-4 py-3">
                <span class="text-sm text-gray-500">Nama</span>
                <span class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3">
                <span class="text-sm text-gray-500">Status</span>
                <span class="text-sm font-bold" id="success-status"></span>
            </div>
            <div class="flex justify-between items-center px-4 py-3">
                <span class="text-sm text-gray-500">Waktu</span>
                <span class="text-sm text-gray-900" id="success-time"></span>
            </div>
        </div>

        {{-- Peringatan terlambat --}}
        <div id="late-warning" class="hidden mb-4 bg-amber-900/30 border border-amber-500/30 text-amber-300 px-4 py-3 rounded-xl text-sm text-left">
            <p class="font-semibold mb-0.5">⚠️ Kamu terlambat!</p>
            <p class="text-amber-400/80 text-xs">Keterlambatan ini tercatat. Usahakan hadir tepat waktu berikutnya.</p>
        </div>

        <a href="{{ route('siswa.attendance.history') }}"
           class="block text-blue-600 hover:text-blue-700 text-sm font-medium transition-colors">
            Lihat Riwayat Absensi →
        </a>
    </div>

    {{-- ── State: Gagal ── --}}
    <div id="state-error" class="text-center hidden">
        <div class="w-20 h-20 rounded-full bg-red-500/10 border-2 border-red-500/20 flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Absensi Gagal</h2>
        <p class="text-gray-500 text-sm mb-6" id="error-msg"></p>
        <button onclick="showState('ready')"
                class="w-full bg-white hover:bg-gray-100 text-gray-600 font-medium py-3.5 rounded-2xl border border-gray-200 transition-colors">
            Coba Lagi
        </button>
    </div>

</div>

<script>
(function() {
    const TOKEN = @json($token);
    const CSRF  = document.querySelector('meta[name="csrf-token"]').content;
    let   gps   = null;

    const STATES = ['loading', 'gps-error', 'ready', 'submitting', 'success', 'error'];

    function showState(name) {
        STATES.forEach(s => {
            const el = document.getElementById('state-' + s);
            if (el) el.classList.toggle('hidden', s !== name);
        });
    }

    function requestGPS() {
        showState('loading');

        if (!navigator.geolocation) {
            document.getElementById('gps-error-msg').textContent = 'Browser tidak mendukung GPS. Gunakan browser lain.';
            showState('gps-error');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                gps = {
                    latitude:     pos.coords.latitude,
                    longitude:    pos.coords.longitude,
                    gps_accuracy: pos.coords.accuracy,
                };
                const accEl = document.getElementById('gps-accuracy-text');
                if (accEl) accEl.textContent = '±' + Math.round(gps.gps_accuracy) + 'm';
                showState('ready');
            },
            function(err) {
                const MSGS = {
                    1: 'Akses lokasi ditolak. Buka pengaturan browser dan izinkan lokasi.',
                    2: 'Posisi tidak tersedia. Pastikan GPS aktif dan di area terbuka.',
                    3: 'GPS timeout. Pindah ke area terbuka dan coba lagi.',
                };
                document.getElementById('gps-error-msg').textContent = MSGS[err.code] || 'Gagal mendeteksi lokasi.';
                showState('gps-error');
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    }

    document.getElementById('btn-absen')?.addEventListener('click', async function() {
        if (!gps) { requestGPS(); return; }
        showState('submitting');

        try {
            const res = await fetch('{{ route("siswa.attendance.submit") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body:    JSON.stringify({ token: TOKEN, ...gps }),
            });
            const data = await res.json();

            if (data.success) {
                const titleEl    = document.getElementById('success-title');
                const subtitleEl = document.getElementById('success-subtitle');
                const statusEl   = document.getElementById('success-status');
                const timeEl     = document.getElementById('success-time');
                const lateEl     = document.getElementById('late-warning');

                titleEl.textContent    = data.is_late ? 'Terlambat!' : 'Hadir Tepat Waktu!';
                subtitleEl.textContent = data.message;
                subtitleEl.className   = data.is_late ? 'text-sm mb-6 text-amber-400' : 'text-sm mb-6 text-blue-600';
                statusEl.textContent   = data.status_label;
                statusEl.className     = data.is_late ? 'text-sm font-bold text-amber-400' : 'text-sm font-bold text-blue-600';
                timeEl.textContent     = data.scanned_at + ' WIB';

                if (data.is_late) lateEl.classList.remove('hidden');

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

    // Mulai minta GPS otomatis
    requestGPS();

    // Expose untuk tombol retry
    window.requestGPS  = requestGPS;
    window.showState   = showState;
})();
</script>

</body>
</html>