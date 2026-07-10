<x-simans-layout title="Absensi Saya">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Absensi Saya</h1>
            <p class="text-gray-400 text-sm mt-1">{{ today()->translatedFormat('l, d F Y') }}</p>
        </div>
        <a href="{{ route('guru.teacher-attendance.rewards') }}"
           class="flex items-center gap-2 text-sm text-gray-400 hover:text-white bg-gray-800 border border-white/10 px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
            </svg>
            Poin Reward
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-300 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-900/30 border border-red-700/40 text-red-300 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Ringkasan poin --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-gray-900 border border-white/5 rounded-xl p-4 text-center">
            <p class="text-3xl font-bold text-emerald-400">{{ $pointsThisMonth }}</p>
            <p class="text-xs text-gray-500 mt-1">Poin Bulan Ini</p>
        </div>
        <div class="bg-gray-900 border border-white/5 rounded-xl p-4 text-center">
            <p class="text-3xl font-bold text-white">{{ $pointsTotal }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Poin</p>
        </div>
    </div>

    {{-- Sesi absensi hari ini --}}
    @if($sessions->isEmpty())
        <div class="bg-gray-900 border border-white/5 rounded-xl p-10 text-center mb-5">
            <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            <h2 class="text-lg font-semibold text-white mb-1">Belum Ada Sesi Hari Ini</h2>
            <p class="text-gray-400 text-sm">Sesi absensi dibuat otomatis oleh sistem.</p>
        </div>
    @else
        @foreach($sessions as $session)
            @php
                $myAttendance = $session->attendances->first();
                $isOpen       = $session->isOpen();
                $label        = $session->session_type === 'masuk' ? 'Absen Masuk' : 'Absen Pulang';
                $colors = ['hadir'=>'emerald','terlambat'=>'amber','izin'=>'blue','sakit'=>'purple','dinas'=>'cyan','alfa'=>'red'];
                $labels = ['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','dinas'=>'Perjalanan Dinas','alfa'=>'Alfa'];
            @endphp

            <div class="bg-gray-900 border border-white/5 rounded-xl mb-4 overflow-hidden">
                {{-- Header sesi --}}
                <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-white">{{ $label }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ substr($session->open_time,0,5) }} – {{ substr($session->close_time,0,5) }} WIB
                            @if($session->late_after && $session->session_type === 'masuk')
                                &middot; Terlambat setelah {{ substr($session->late_after,0,5) }}
                            @endif
                        </p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full border
                        {{ $isOpen ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : 'text-gray-500 bg-gray-800 border-white/10' }}">
                        {{ $isOpen ? 'Buka' : 'Tutup' }}
                    </span>
                </div>

                {{-- Konten --}}
                <div class="p-5">
                    @if($myAttendance)
                        {{-- Sudah absen --}}
                        @php $c = $colors[$myAttendance->status] ?? 'gray'; @endphp
                        <div class="flex items-center gap-3 py-3 px-4 bg-{{ $c }}-500/10 border border-{{ $c }}-500/20 rounded-xl">
                            <svg class="w-5 h-5 text-{{ $c }}-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-bold text-{{ $c }}-400">{{ $labels[$myAttendance->status] ?? $myAttendance->status }}</p>
                                @if($myAttendance->scanned_at)
                                    <p class="text-xs text-{{ $c }}-400/60">Tercatat pukul {{ $myAttendance->scanned_at->format('H:i:s') }} WIB</p>
                                @endif
                                @if($myAttendance->notes)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $myAttendance->notes }}</p>
                                @endif
                            </div>
                        </div>

                    @elseif($isOpen)
                        {{-- Tombol scan QR --}}
                        <button data-session-id="{{ $session->id }}" data-session-type="{{ $session->session_type }}"
                                class="btn-start-scan w-full flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 rounded-2xl transition-colors text-base mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                            </svg>
                            Scan QR {{ $label }}
                        </button>
                        <p class="text-center text-xs text-gray-500 mb-4">Scan QR yang ditempel di kantor guru</p>

                        {{-- Izin / Sakit / Dinas --}}
                        <div x-data="{ showForm: false }">
                            <button type="button" @click="showForm=!showForm"
                                    class="w-full text-xs text-gray-400 hover:text-white text-center py-2 border border-white/10 rounded-xl transition-colors">
                                Izin / Sakit / Perjalanan Dinas
                            </button>
                            <div x-show="showForm" x-cloak class="mt-3">
                                <form method="POST" action="{{ route('guru.teacher-attendance.submit-status') }}"
                                      enctype="multipart/form-data" class="space-y-2">
                                    @csrf
                                    <input type="hidden" name="session_id" value="{{ $session->id }}">
                                    <select name="status" required
                                            class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                        <option value="izin">Izin</option>
                                        <option value="sakit">Sakit</option>
                                        <option value="dinas">Perjalanan Dinas</option>
                                    </select>
                                    <input type="text" name="notes" placeholder="Keterangan (opsional)"
                                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                    <input type="file" name="attachment" accept="image/*,.pdf"
                                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-1.5 text-xs transition-colors file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-gray-700 file:text-gray-300">
                                    <p class="text-xs text-gray-600">Lampirkan surat/bukti (opsional)</p>
                                    <button type="submit"
                                            class="w-full bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                                        Kirim
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-gray-500">Sesi belum dibuka atau sudah ditutup.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    {{-- Area scanner kamera (shared untuk semua sesi) --}}
    <div id="scanner-area" class="hidden mb-5">
        <div class="bg-gray-900 border border-emerald-500/20 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
                <span class="text-sm font-semibold text-emerald-400">Arahkan ke QR Absensi Guru</span>
                <button id="btn-stop-scan"
                        class="text-gray-400 hover:text-white text-xs py-1 px-3 bg-gray-800 rounded-lg border border-white/10 transition-colors">
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
                        <div id="scan-line" class="absolute left-2 right-2 h-0.5 bg-emerald-400/80 rounded-full top-1/2"></div>
                    </div>
                </div>
            </div>
            <div class="px-4 py-3 text-center">
                <p id="scan-status" class="text-sm text-gray-400">Mendeteksi QR Code...</p>
            </div>
        </div>
    </div>

    {{-- GPS + tombol absen --}}
    <div id="gps-area" class="hidden mb-5">
        <div class="bg-gray-900 border border-blue-500/20 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-4 p-3 rounded-xl bg-gray-800 border border-white/10" id="gps-box">
                <svg class="w-4 h-4 text-blue-400 animate-pulse flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                <span id="gps-status-text" class="text-sm text-gray-300">Mendeteksi lokasi GPS...</span>
            </div>
            <button id="btn-absen" disabled
                    class="w-full bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3.5 rounded-2xl transition-all">
                Absen Sekarang
            </button>
            <p id="absen-error" class="text-red-400 text-xs text-center mt-2 hidden"></p>
        </div>
    </div>

    {{-- Riwayat --}}
    <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5">
            <h2 class="text-sm font-semibold text-white">Riwayat 30 Hari Terakhir</h2>
        </div>
        @if($history->isEmpty())
            <div class="px-5 py-8 text-center">
                <p class="text-gray-500 text-sm">Belum ada riwayat absensi.</p>
            </div>
        @else
            <div class="divide-y divide-white/5">
                @foreach($history as $rec)
                    @php
                        $colors = ['hadir'=>'emerald','terlambat'=>'amber','izin'=>'blue','sakit'=>'purple','dinas'=>'cyan','alfa'=>'red'];
                        $c = $colors[$rec->status] ?? 'gray';
                    @endphp
                    <div class="flex items-center gap-3 px-5 py-3.5">
                        <div class="w-2 h-2 rounded-full bg-{{ $c }}-400 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white">
                                {{ $rec->session->session_type === 'masuk' ? 'Masuk' : 'Pulang' }}
                                &middot; <span class="text-{{ $c }}-400 font-semibold">{{ $rec->statusLabel }}</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $rec->session->session_date->translatedFormat('d M Y') }}
                                @if($rec->scanned_at) &middot; {{ $rec->scanned_at->format('H:i') }} @endif
                                @if($rec->notes) &middot; {{ $rec->notes }} @endif
                            </p>
                        </div>
                        @if($rec->distance_meters !== null)
                            <span class="text-xs text-gray-600 flex-shrink-0">{{ round($rec->distance_meters) }}m</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <style>
    @keyframes scanLine {
        0%, 100% { top: 8%; }
        50%       { top: 88%; }
    }
    #scan-line { animation: scanLine 2s ease-in-out infinite; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
    (function() {
        'use strict';

        var CSRF       = document.querySelector('meta[name="csrf-token"]').content;
        var SUBMIT_URL = '{{ route("guru.teacher-attendance.scan") }}';

        var currentSessionId = null;
        var scannedToken     = null;
        var gpsData          = null;
        var videoStream      = null;
        var scanInterval     = null;

        var scanArea  = document.getElementById('scanner-area');
        var gpsArea   = document.getElementById('gps-area');
        var video     = document.getElementById('qr-video');
        var canvas    = document.getElementById('qr-canvas');
        var scanStat  = document.getElementById('scan-status');
        var gpsStat   = document.getElementById('gps-status-text');
        var gpsBox    = document.getElementById('gps-box');
        var btnStop   = document.getElementById('btn-stop-scan');
        var btnAbsen  = document.getElementById('btn-absen');
        var errorEl   = document.getElementById('absen-error');

        // Klik tombol scan
        document.querySelectorAll('.btn-start-scan').forEach(function(btn) {
            btn.addEventListener('click', function() {
                currentSessionId = this.dataset.sessionId;
                scannedToken     = null;
                gpsData          = null;
                scanArea.classList.remove('hidden');
                gpsArea.classList.add('hidden');
                this.closest('.bg-gray-900').querySelector('.p-5').style.display = 'none';
                scanStat.textContent = 'Mendeteksi QR Code...';
                scanStat.className   = 'text-sm text-gray-400';
                startCamera();
            });
        });

        // Stop scan
        if (btnStop) {
            btnStop.addEventListener('click', function() {
                stopCamera();
                scanArea.classList.add('hidden');
                document.querySelectorAll('.p-5').forEach(function(el) { el.style.display = ''; });
            });
        }

        function startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                scanStat.textContent = 'Browser tidak mendukung kamera. Gunakan Chrome/Safari terbaru.';
                scanStat.className   = 'text-sm text-red-400';
                return;
            }
            navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 640 }, height: { ideal: 640 } }
            })
            .then(function(stream) {
                videoStream     = stream;
                video.srcObject = stream;
                video.play();
                video.addEventListener('loadedmetadata', function() {
                    canvas.width  = video.videoWidth;
                    canvas.height = video.videoHeight;
                    scanStat.textContent = 'Arahkan ke QR absensi guru di kantor...';
                    scanInterval = setInterval(scanFrame, 250);
                });
            })
            .catch(function(err) {
                var msg = 'Kamera tidak bisa dibuka.';
                if (err.name === 'NotAllowedError')   msg = 'Izin kamera ditolak. Buka pengaturan browser.';
                if (err.name === 'NotFoundError')     msg = 'Kamera tidak ditemukan.';
                if (err.name === 'NotSupportedError') msg = 'HTTPS diperlukan untuk kamera.';
                scanStat.textContent = msg;
                scanStat.className   = 'text-sm text-red-400';
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
            var code = jsQR(ctx.getImageData(0, 0, canvas.width, canvas.height).data, canvas.width, canvas.height, {
                inversionAttempts: 'dontInvert',
            });
            if (!code) return;

            var raw = code.data;
            stopCamera();
            scanArea.classList.add('hidden');
            document.querySelectorAll('.p-5').forEach(function(el) { el.style.display = ''; });

            // Cek apakah QR berisi URL absensi guru
            // Format: https://domain.com/absensi-guru/{token}
            var tokenMatch = raw.match(/\/absensi-guru\/([a-zA-Z0-9]+)/);
            if (tokenMatch) {
                scannedToken = tokenMatch[1]; // ambil token dari URL
            } else {
                scannedToken = raw; // token mentah (fallback)
            }

            gpsArea.classList.remove('hidden');
            btnAbsen.disabled = true;
            requestGPS();
        }

        function requestGPS() {
            gpsStat.textContent = 'Mendeteksi lokasi GPS...';
            gpsStat.className   = 'text-sm text-gray-300';
            gpsBox.className    = 'flex items-center gap-3 mb-4 p-3 rounded-xl bg-gray-800 border border-white/10';

            if (!navigator.geolocation) {
                gpsStat.textContent = 'Browser tidak mendukung GPS.';
                gpsStat.className   = 'text-sm text-red-400';
                btnAbsen.disabled   = false;
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    gpsData = { latitude: pos.coords.latitude, longitude: pos.coords.longitude };
                    gpsStat.textContent = 'Lokasi terdeteksi (\u00b1' + Math.round(pos.coords.accuracy) + 'm) \u2014 siap absen';
                    gpsStat.className   = 'text-sm text-emerald-400';
                    gpsBox.className    = 'flex items-center gap-3 mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20';
                    btnAbsen.disabled   = false;
                },
                function(err) {
                    var msgs = { 1: 'Izinkan akses lokasi.', 2: 'GPS tidak tersedia.', 3: 'GPS timeout.' };
                    gpsStat.textContent = msgs[err.code] || 'Gagal deteksi lokasi.';
                    gpsStat.className   = 'text-sm text-red-400';
                    gpsBox.className    = 'flex items-center gap-3 mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20';
                    // Tetap izinkan absen tanpa GPS
                    btnAbsen.disabled   = false;
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }

        // Submit absensi
        if (btnAbsen) {
            btnAbsen.addEventListener('click', async function() {
                if (!scannedToken) { alert('Token tidak ditemukan. Scan ulang QR.'); return; }
                this.disabled    = true;
                this.textContent = 'Menyimpan...';
                errorEl.classList.add('hidden');

                try {
                    var res  = await fetch(SUBMIT_URL, {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body:    JSON.stringify({
                            qr_token:  scannedToken,
                            latitude:  gpsData ? gpsData.latitude  : null,
                            longitude: gpsData ? gpsData.longitude : null,
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

</x-simans-layout>