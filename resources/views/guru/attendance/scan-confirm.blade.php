<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi Guru — {{ $school->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #111827; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        {{-- Header --}}
        <div class="text-center mb-6">
            <p class="text-gray-500 text-sm">{{ $school->name }}</p>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Absensi Guru</h1>
            <p class="text-gray-500 text-sm mt-1">
                {{ $session->session_type === 'masuk' ? 'Absen Masuk' : 'Absen Pulang' }}
                &middot; {{ now()->format('H:i') }} WIB
            </p>
        </div>

        {{-- Info sesi --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-600/10 border border-blue-200 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-900 font-semibold">{{ auth()->user()->name }}</p>
                    <p class="text-gray-500 text-sm">
                        {{ substr($session->open_time, 0, 5) }} – {{ substr($session->close_time, 0, 5) }} WIB
                        @if($session->late_after && $session->session_type === 'masuk')
                            &middot; Terlambat setelah {{ substr($session->late_after, 0, 5) }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="bg-red-900/30 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl text-sm mb-4">
                {{ session('error') }}
            </div>
        @endif

        {{-- GPS status --}}
        <div id="gps-box" class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl p-4 mb-4">
            <svg id="gps-icon" class="w-5 h-5 text-blue-400 animate-pulse flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
            <p id="gps-status" class="text-sm text-gray-600">Mendeteksi lokasi GPS...</p>
        </div>

        <form id="form-absen" method="POST" action="{{ route('teacher.attendance.confirm') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="latitude" id="inp-lat">
            <input type="hidden" name="longitude" id="inp-lng">

            <button type="submit" id="btn-absen" disabled
                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-gray-900 font-bold py-4 rounded-2xl text-base transition-all">
                Absen Sekarang
            </button>
        </form>

        <a href="{{ route('guru.teacher-attendance.index') }}"
           class="block text-center text-sm text-gray-400 hover:text-gray-600 mt-4 transition-colors">
            Kembali ke halaman absensi
        </a>
    </div>

    <script>
    (function() {
        var btnAbsen = document.getElementById('btn-absen');
        var gpsStatus = document.getElementById('gps-status');
        var gpsBox    = document.getElementById('gps-box');
        var inpLat    = document.getElementById('inp-lat');
        var inpLng    = document.getElementById('inp-lng');

        if (!navigator.geolocation) {
            gpsStatus.textContent = 'GPS tidak didukung. Klik absen tanpa GPS.';
            btnAbsen.disabled = false;
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                inpLat.value = pos.coords.latitude;
                inpLng.value = pos.coords.longitude;
                gpsStatus.textContent = 'Lokasi terdeteksi (\u00b1' + Math.round(pos.coords.accuracy) + 'm) \u2014 siap absen';
                gpsStatus.className = 'text-sm text-blue-600';
                gpsBox.className = 'flex items-center gap-3 bg-emerald-900/20 border border-blue-200 rounded-xl p-4 mb-4';
                btnAbsen.disabled = false;
            },
            function(err) {
                gpsStatus.textContent = 'GPS tidak terdeteksi. Klik absen untuk lanjut.';
                gpsStatus.className = 'text-sm text-amber-400';
                gpsBox.className = 'flex items-center gap-3 bg-amber-900/20 border border-amber-500/20 rounded-xl p-4 mb-4';
                btnAbsen.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    })();
    </script>
</body>
</html>
