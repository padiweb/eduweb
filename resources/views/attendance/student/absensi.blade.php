<x-simans-layout title="Absensi Saya">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Absensi Saya</h1>
        <p class="text-gray-500 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    @php
        $student      = auth()->user();
        $classroom    = $student->classrooms()
                            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                            ->first();
        $session      = $classroom
            ? \App\Models\AttendanceSession::where('classroom_id', $classroom->id)
                ->whereDate('session_date', today())
                ->first()
            : null;
        $myAttendance = $session
            ? \App\Models\Attendance::where('session_id', $session->id)
                ->where('student_id', $student->id)
                ->first()
            : null;
        $colorMap = ['hadir'=>'emerald','terlambat'=>'amber','izin'=>'blue','sakit'=>'blue','alfa'=>'red'];
        $labelMap = ['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alfa'=>'Alfa'];
    @endphp

    {{-- Status absensi hari ini --}}
    @if($myAttendance)
        @php $color = $colorMap[$myAttendance->status] ?? 'gray'; @endphp
        <div class="bg-white border border-{{ $color }}-200 rounded-xl p-6 mb-5 text-center">
            <div class="w-16 h-16 rounded-full bg-{{ $color }}-50 border-2 border-{{ $color }}-200 flex items-center justify-center mx-auto mb-4">
                @if($myAttendance->status === 'hadir')
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @elseif($myAttendance->status === 'terlambat')
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                @else
                    <svg class="w-8 h-8 text-{{ $color }}-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @endif
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $labelMap[$myAttendance->status] ?? $myAttendance->status }}</h2>
            <p class="text-gray-500 text-sm">
                @if($myAttendance->scanned_at)
                    Tercatat pukul {{ $myAttendance->scanned_at->format('H:i:s') }} WIB
                @else
                    Input manual oleh guru
                @endif
            </p>
            @if($myAttendance->status === 'terlambat')
                <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-700">
                    Kamu tercatat terlambat. Harap lebih tepat waktu besok!
                </div>
            @endif
            @if($myAttendance->status === 'alfa')
                <div class="mt-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                    Kamu tercatat tidak hadir. Hubungi guru jika ada kesalahan.
                </div>
            @endif
        </div>

    @elseif($session && ! $session->is_closed)
        {{-- Belum absen, sesi masih aktif --}}
        <div class="bg-white border border-amber-200 rounded-xl p-5 mb-5">
            <div class="text-center mb-5">
                <p class="text-sm font-semibold text-gray-900">{{ $classroom->name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    Jam: {{ substr($session->open_time,0,5) }} – {{ substr($session->close_time,0,5) }} WIB
                </p>
            </div>

            {{-- Tombol scan QR --}}
            <button id="btn-start-scan"
                    class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition-colors text-base mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                </svg>
                Scan QR Absensi
            </button>
            <p class="text-center text-xs text-gray-500">
                Atau scan QR di papan kelas langsung dari kamera HP
            </p>
        </div>

        {{-- Area scanner kamera --}}
        <div id="scanner-area" class="hidden mb-5">
            <div class="bg-white border border-blue-200 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                    <span class="text-sm font-semibold text-blue-600">Arahkan ke QR Code</span>
                    <button id="btn-stop-scan"
                            class="text-gray-500 hover:text-blue-600 text-xs py-1 px-3 bg-white rounded-lg border border-gray-200 transition-colors">
                        Batal
                    </button>
                </div>
                <div class="relative bg-black" style="aspect-ratio:1/1">
                    <video id="qr-video" class="w-full h-full object-cover" playsinline autoplay muted></video>
                    <canvas id="qr-canvas" class="hidden absolute inset-0"></canvas>
                    {{-- Overlay sudut --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-56 h-56 relative">
                            <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-emerald-400 rounded-tl-lg"></div>
                            <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-emerald-400 rounded-tr-lg"></div>
                            <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-emerald-400 rounded-bl-lg"></div>
                            <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-emerald-400 rounded-br-lg"></div>
                            <div id="scan-line" class="absolute left-2 right-2 h-0.5 bg-blue-50 rounded-full top-1/2"></div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 text-center">
                    <p id="scan-status" class="text-sm text-gray-500">Mendeteksi QR Code...</p>
                </div>
            </div>
        </div>

        {{-- GPS + tombol absen (muncul setelah QR terdeteksi) --}}
        <div id="gps-area" class="hidden mb-5">
            <div class="bg-white border border-blue-200 rounded-xl p-4">
                <div class="flex items-center gap-3 mb-4 p-3 rounded-xl bg-white border border-gray-200" id="gps-box">
                    <svg class="w-4 h-4 text-blue-600 animate-pulse flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                    </svg>
                    <span id="gps-status-text" class="text-sm text-gray-500">Mendeteksi lokasi GPS...</span>
                </div>
                <button id="btn-absen"
                        disabled
                        style="width:100%;padding:14px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;font-weight:700;border-radius:12px;border:none;cursor:pointer;font-size:15px;box-shadow:0 3px 10px rgba(59,130,246,0.35)">
                    Absen Sekarang
                </button>
                <p id="absen-error" class="text-red-600 text-xs text-center mt-2 hidden"></p>
            </div>
        </div>

    @elseif($session && $session->is_closed)
        <div class="bg-white border border-red-200 rounded-xl p-6 mb-5 text-center">
            <svg class="w-12 h-12 text-red-600 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Absensi Ditutup</h2>
            <p class="text-gray-500 text-sm">Sesi absensi hari ini sudah ditutup.</p>
            <p class="text-gray-500 text-xs mt-1">Hubungi guru untuk absen manual.</p>
        </div>

    @else
        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-5 text-center">
            <svg class="w-12 h-12 text-gray-500 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Belum Ada Sesi Hari Ini</h2>
            <p class="text-gray-500 text-sm">Absensi belum dibuka. Coba lagi nanti.</p>
        </div>
    @endif

    {{-- Link riwayat --}}
    <a href="{{ route('siswa.attendance.history') }}"
       class="flex items-center justify-between bg-white border border-gray-200 rounded-xl px-5 py-4 hover:bg-gray-50 transition-colors">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            <span class="text-sm font-medium text-gray-900">Lihat Riwayat Absensi</span>
        </div>
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
    </a>

    <style>
    @keyframes scanLine {
        0%, 100% { top: 8%; }
        50%       { top: 88%; }
    }
    #scan-line { animation: scanLine 2s ease-in-out infinite; }
    </style>

    @if($session && ! $session->is_closed && ! $myAttendance)
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
    (function() {
        'use strict';

        var CSRF       = document.querySelector('meta[name="csrf-token"]').content;
        var SUBMIT_URL = '{{ route("siswa.attendance.submit") }}';
        var APP_URL    = '{{ rtrim(config("app.url"), "/") }}';

        var scannedToken = null;
        var gpsData      = null;
        var videoStream  = null;
        var scanInterval = null;

        var btnStart  = document.getElementById('btn-start-scan');
        var btnStop   = document.getElementById('btn-stop-scan');
        var btnAbsen  = document.getElementById('btn-absen');
        var scanArea  = document.getElementById('scanner-area');
        var gpsArea   = document.getElementById('gps-area');
        var video     = document.getElementById('qr-video');
        var canvas    = document.getElementById('qr-canvas');
        var scanStat  = document.getElementById('scan-status');
        var gpsStat   = document.getElementById('gps-status-text');
        var gpsBox    = document.getElementById('gps-box');
        var errorEl   = document.getElementById('absen-error');

        // Buka scanner
        if (btnStart) {
            btnStart.addEventListener('click', function() {
                scanArea.classList.remove('hidden');
                btnStart.parentElement.classList.add('hidden');
                startCamera();
            });
        }

        // Stop scanner
        if (btnStop) {
            btnStop.addEventListener('click', function() {
                stopCamera();
                scanArea.classList.add('hidden');
                btnStart.parentElement.classList.remove('hidden');
                scanStat.textContent = 'Mendeteksi QR Code...';
                scanStat.className   = 'text-sm text-gray-500';
            });
        }

        function startCamera() {
            scanStat.textContent = 'Membuka kamera...';
            scanStat.className   = 'text-sm text-gray-500';

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                scanStat.textContent = 'Browser tidak mendukung kamera. Gunakan Chrome/Safari terbaru.';
                scanStat.className   = 'text-sm text-red-600';
                return;
            }

            navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 640 }, height: { ideal: 640 } }
            })
            .then(function(stream) {
                videoStream    = stream;
                video.srcObject = stream;
                video.play();
                video.addEventListener('loadedmetadata', function() {
                    canvas.width  = video.videoWidth;
                    canvas.height = video.videoHeight;
                    scanStat.textContent = 'Arahkan ke QR Code di papan kelas...';
                    scanInterval = setInterval(scanFrame, 250);
                });
            })
            .catch(function(err) {
                var msg = 'Kamera tidak bisa dibuka.';
                if (err.name === 'NotAllowedError')  msg = 'Izin kamera ditolak. Buka pengaturan browser dan izinkan kamera.';
                if (err.name === 'NotFoundError')    msg = 'Kamera tidak ditemukan di perangkat ini.';
                if (err.name === 'NotSupportedError') msg = 'HTTPS diperlukan untuk menggunakan kamera.';
                scanStat.textContent = msg;
                scanStat.className   = 'text-sm text-red-600';
            });
        }

        function stopCamera() {
            if (scanInterval) { clearInterval(scanInterval); scanInterval = null; }
            if (videoStream)  { videoStream.getTracks().forEach(function(t) { t.stop(); }); videoStream = null; }
        }

        function scanFrame() {
            if (!video || video.readyState !== video.HAVE_ENOUGH_DATA) return;

            var ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            var code      = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'dontInvert',
            });

            if (!code) return;

            var rawUrl = code.data;
            var token  = null;

            try {
                var parsed = new URL(rawUrl);

                // Format 1: URL dengan token — /absensi/scan?token=xxx
                token = parsed.searchParams.get('token');

                // Format 2: URL permanen per kelas — /absensi/kelas/{slug}
                // Redirect ke URL tersebut agar server resolve token aktif
                if (!token && parsed.pathname.indexOf('/absensi/kelas/') !== -1) {
                    stopCamera();
                    scanStat.textContent = 'QR terdeteksi! Membuka halaman absensi...';
                    scanStat.className   = 'text-sm text-blue-600';
                    // Redirect ke halaman class-scan yang sudah handle login
                    window.location.href = rawUrl;
                    return;
                }
            } catch(e) {
                // Bukan URL valid
            }

            if (token) {
                scannedToken = token;
                stopCamera();
                scanArea.classList.add('hidden');
                scanStat.textContent = 'QR berhasil dibaca!';
                // Tampilkan GPS
                gpsArea.classList.remove('hidden');
                requestGPS();
            } else {
                scanStat.textContent = 'QR tidak dikenali. Pastikan scan QR dari papan kelas SiManS.';
                scanStat.className   = 'text-sm text-amber-600';
            }
        }

        // Request GPS
        function requestGPS() {
            if (!navigator.geolocation) {
                gpsStat.textContent = 'Browser tidak mendukung GPS.';
                gpsStat.className   = 'text-sm text-red-600';
                return;
            }
            gpsStat.textContent = 'Mendeteksi lokasi GPS...';
            gpsStat.className   = 'text-sm text-gray-500';

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    gpsData = { latitude: pos.coords.latitude, longitude: pos.coords.longitude, gps_accuracy: pos.coords.accuracy };
                    gpsStat.textContent = 'Lokasi terdeteksi (\u00b1' + Math.round(pos.coords.accuracy) + 'm) \u2014 siap absen';
                    gpsStat.className   = 'text-sm text-blue-600';
                    gpsBox.className    = 'flex items-center gap-3 mb-4 p-3 rounded-xl bg-blue-50 border border-blue-200';
                    btnAbsen.disabled   = false;
                },
                function(err) {
                    var msgs = { 1: 'Izinkan akses lokasi di browser.', 2: 'GPS tidak tersedia. Aktifkan GPS.', 3: 'GPS timeout. Coba lagi.' };
                    gpsStat.textContent = msgs[err.code] || 'Gagal deteksi lokasi.';
                    gpsStat.className   = 'text-sm text-red-600';
                    gpsBox.className    = 'flex items-center gap-3 mb-4 p-3 rounded-xl bg-red-50 border border-red-200';
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }

        // Submit absensi
        if (btnAbsen) {
            btnAbsen.addEventListener('click', async function() {
                if (!scannedToken) { alert('Token tidak ditemukan. Scan ulang QR.'); return; }
                if (!gpsData)      { alert('GPS belum terdeteksi.'); return; }

                this.disabled    = true;
                this.textContent = 'Menyimpan...';
                errorEl.classList.add('hidden');

                try {
                    var res  = await fetch(SUBMIT_URL, {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body:    JSON.stringify({
                            token:        scannedToken,
                            latitude:     gpsData.latitude,
                            longitude:    gpsData.longitude,
                            gps_accuracy: gpsData.gps_accuracy,
                        }),
                    });
                    var data = await res.json();

                    if (data.success) {
                        location.reload();
                    } else {
                        errorEl.textContent = data.message || 'Gagal menyimpan absensi.';
                        errorEl.classList.remove('hidden');
                        this.disabled    = false;
                        this.textContent = 'Absen Sekarang';
                    }
                } catch(e) {
                    errorEl.textContent = 'Koneksi gagal. Periksa internet.';
                    errorEl.classList.remove('hidden');
                    this.disabled    = false;
                    this.textContent = 'Absen Sekarang';
                }
            });
        }

    })();
    </script>
    @endif

</x-simans-layout>