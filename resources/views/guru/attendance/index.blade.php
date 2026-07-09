<x-simans-layout title="Absensi Saya">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Absensi Saya</h1>
            <p class="text-gray-400 text-sm mt-1">{{ today()->translatedFormat('l, d F Y') }}</p>
        </div>
        <a href="{{ route('guru.attendance.rewards') }}"
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
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-white mb-3">Sesi Hari Ini</h2>

        @if($sessions->isEmpty())
            <div class="bg-gray-900 border border-white/5 rounded-xl p-6 text-center">
                <p class="text-gray-500 text-sm">Belum ada sesi absensi hari ini.</p>
                <p class="text-gray-600 text-xs mt-1">Sesi dibuat otomatis oleh sistem sesuai jadwal sekolah.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($sessions as $session)
                    @php
                        $myAttendance = $session->attendances->first();
                        $isOpen = $session->isOpen();
                        $label  = $session->session_type === 'masuk' ? 'Absen Masuk' : 'Absen Pulang';
                    @endphp
                    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-sm font-bold text-white">{{ $label }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ substr($session->open_time,0,5) }} — {{ substr($session->close_time,0,5) }}
                                    @if($session->late_after && $session->session_type === 'masuk')
                                        &middot; Terlambat setelah {{ substr($session->late_after,0,5) }}
                                    @endif
                                </p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full border
                                {{ $isOpen ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : 'text-gray-500 bg-gray-800 border-white/10' }}">
                                {{ $isOpen ? 'Buka' : 'Tutup' }}
                            </span>
                        </div>

                        @if($myAttendance)
                            {{-- Sudah absen --}}
                            @php
                                $colors = [
                                    'hadir'     => 'emerald',
                                    'terlambat' => 'amber',
                                    'izin'      => 'blue',
                                    'sakit'     => 'purple',
                                    'dinas'     => 'cyan',
                                    'alfa'      => 'red',
                                ];
                                $c = $colors[$myAttendance->status] ?? 'gray';
                            @endphp
                            <div class="flex items-center gap-2 py-2 px-3 bg-{{ $c }}-500/10 border border-{{ $c }}-500/20 rounded-xl">
                                <svg class="w-4 h-4 text-{{ $c }}-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-{{ $c }}-400">{{ $myAttendance->statusLabel }}</p>
                                    @if($myAttendance->scanned_at)
                                        <p class="text-xs text-{{ $c }}-400/60">{{ $myAttendance->scanned_at->format('H:i') }}</p>
                                    @endif
                                </div>
                            </div>
                        @elseif($isOpen)
                            {{-- Tombol scan + izin/sakit/dinas --}}
                            <div class="space-y-2">
                                <button onclick="startScan({{ $session->id }}, '{{ $session->qr_token }}')"
                                        class="w-full flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75z"/>
                                    </svg>
                                    Scan QR Absen
                                </button>

                                <div x-data="{ showForm: false }">
                                    <button type="button" @click="showForm=!showForm"
                                            class="w-full text-xs text-gray-400 hover:text-white text-center py-1.5 transition-colors">
                                        Izin / Sakit / Dinas
                                    </button>
                                    <div x-show="showForm" x-cloak class="mt-2">
                                        <form method="POST" action="{{ route('guru.attendance.submit-status') }}"
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
                                                    class="w-full bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold py-2 rounded-xl transition-colors">
                                                Kirim
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-gray-600 text-center py-2">Sesi belum dibuka atau sudah tutup.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Riwayat absensi --}}
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

    {{-- Scanner QR (jsQR) --}}
    <div id="scanner-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/90 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-sm overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-white/5">
                <p class="font-semibold text-white text-sm">Scan QR Absensi</p>
                <button onclick="stopScan()" class="text-gray-500 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <div class="relative bg-black rounded-xl overflow-hidden" style="aspect-ratio:1">
                    <video id="qr-video" class="w-full h-full object-cover" playsinline></video>
                    <canvas id="qr-canvas" class="hidden"></canvas>
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-48 h-48 border-2 border-emerald-400 rounded-xl opacity-70"></div>
                    </div>
                </div>
                <div id="scan-result" class="mt-3 text-center text-sm text-gray-400">Arahkan kamera ke QR absensi</div>
                <div id="scan-loading" class="hidden mt-3">
                    <div class="flex items-center justify-center gap-2 text-emerald-400 text-sm">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Memproses...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
    var videoStream = null;
    var scanning    = false;
    var currentSessionId = null;
    var currentToken     = null;
    var CSRF = '{{ csrf_token() }}';

    function startScan(sessionId, token) {
        currentSessionId = sessionId;
        currentToken     = token;
        var modal = document.getElementById('scanner-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('scan-result').textContent = 'Arahkan kamera ke QR absensi';

        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(function(stream) {
                videoStream = stream;
                var video = document.getElementById('qr-video');
                video.srcObject = stream;
                video.play();
                scanning = true;
                requestAnimationFrame(tick);
            })
            .catch(function() {
                // Jika kamera tidak tersedia, langsung submit dengan token yang diketahui
                submitAttendance(token);
            });
    }

    function tick() {
        if (!scanning) return;
        var video  = document.getElementById('qr-video');
        var canvas = document.getElementById('qr-canvas');
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width  = video.videoWidth;
            canvas.height = video.videoHeight;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            var code = jsQR(imageData.data, imageData.width, imageData.height);
            if (code && code.data) {
                scanning = false;
                stopVideo();
                submitAttendance(code.data);
                return;
            }
        }
        requestAnimationFrame(tick);
    }

    function submitAttendance(qrData) {
        document.getElementById('scan-loading').classList.remove('hidden');
        document.getElementById('scan-result').textContent = '';

        // Ambil GPS
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(pos) { doSubmit(qrData, pos.coords.latitude, pos.coords.longitude); },
                function()    { doSubmit(qrData, null, null); },
                { timeout: 5000 }
            );
        } else {
            doSubmit(qrData, null, null);
        }
    }

    function doSubmit(qrToken, lat, lng) {
        fetch('{{ route("guru.attendance.scan") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ qr_token: qrToken, latitude: lat, longitude: lng }),
        })
        .then(r => r.json())
        .then(function(data) {
            document.getElementById('scan-loading').classList.add('hidden');
            var resultEl = document.getElementById('scan-result');
            if (data.success) {
                resultEl.innerHTML = '<span class="text-emerald-400 font-semibold">✓ ' + data.message + ' — ' + data.time + '</span>';
                setTimeout(function() { stopScan(); location.reload(); }, 2000);
            } else {
                resultEl.innerHTML = '<span class="text-red-400">' + data.message + '</span>';
                scanning = true;
                requestAnimationFrame(tick);
                startVideo();
            }
        })
        .catch(function() {
            document.getElementById('scan-result').innerHTML = '<span class="text-red-400">Gagal terhubung ke server.</span>';
            document.getElementById('scan-loading').classList.add('hidden');
        });
    }

    function stopVideo() {
        if (videoStream) { videoStream.getTracks().forEach(t => t.stop()); videoStream = null; }
    }

    function startVideo() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(function(stream) {
                videoStream = stream;
                document.getElementById('qr-video').srcObject = stream;
                document.getElementById('qr-video').play();
                scanning = true;
                requestAnimationFrame(tick);
            });
    }

    function stopScan() {
        scanning = false;
        stopVideo();
        var modal = document.getElementById('scanner-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    </script>

</x-simans-layout>
