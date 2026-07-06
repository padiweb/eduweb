<x-simans-layout title="Absensi Saya">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Absensi Saya</h1>
        <p class="text-gray-400 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    @php
        $student   = auth()->user();
        $classroom = $student->classrooms()->whereHas('academicYear', fn($q) => $q->where('is_active', true))->first();
        $session   = $classroom
            ? \App\Models\AttendanceSession::where('classroom_id', $classroom->id)
                ->whereDate('session_date', today())
                ->first()
            : null;
        $myAttendance = $session
            ? \App\Models\Attendance::where('session_id', $session->id)
                ->where('student_id', $student->id)
                ->first()
            : null;
    @endphp

    @if($myAttendance)
        {{-- Sudah absen hari ini --}}
        @php
            $colorMap = ['hadir'=>'emerald','terlambat'=>'amber','izin'=>'blue','sakit'=>'purple','alfa'=>'red'];
            $color    = $colorMap[$myAttendance->status] ?? 'gray';
            $labelMap = ['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alfa'=>'Alfa'];
        @endphp
        <div class="bg-gray-900 border border-{{ $color }}-500/30 rounded-xl p-6 mb-6 text-center">
            <div class="w-16 h-16 rounded-full bg-{{ $color }}-500/10 border-2 border-{{ $color }}-500/30 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-{{ $color }}-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white mb-1">{{ $labelMap[$myAttendance->status] ?? $myAttendance->status }}</h2>
            <p class="text-gray-400 text-sm">
                @if($myAttendance->scanned_at)
                    Tercatat pukul {{ $myAttendance->scanned_at->format('H:i:s') }} WIB
                @else
                    Input manual oleh guru
                @endif
            </p>
            @if($myAttendance->status === 'terlambat')
                <div class="mt-4 bg-amber-900/20 border border-amber-500/20 rounded-xl px-4 py-3 text-sm text-amber-300">
                    Kamu tercatat terlambat. Harap lebih tepat waktu!
                </div>
            @endif
        </div>

    @elseif($session && ! $session->is_closed && $session->isWithinScanTime())
        {{-- Sesi aktif, belum absen, dalam jam absensi → tampilkan form GPS --}}
        <div class="bg-gray-900 border border-emerald-500/20 rounded-xl p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-white">Absensi Pagi — {{ $classroom->name }}</h2>
                    <p class="text-xs text-gray-400">Buka: {{ substr($session->open_time,0,5) }} · Batas: {{ substr($session->late_after,0,5) }} · Tutup: {{ substr($session->close_time,0,5) }}</p>
                </div>
            </div>

            {{-- Status GPS --}}
            <div id="gps-status" class="flex items-center gap-2 bg-gray-800 border border-white/10 rounded-xl px-4 py-3 mb-4">
                <svg class="w-4 h-4 text-blue-400 animate-pulse flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                <span id="gps-status-text" class="text-sm text-gray-400">Mendeteksi lokasi GPS...</span>
            </div>

            <button id="btn-absen"
                    disabled
                    class="w-full bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-4 rounded-2xl transition-all text-base">
                Absen Sekarang
            </button>

            <p id="absen-error" class="text-red-400 text-xs text-center mt-3 hidden"></p>
        </div>

    @elseif($session && $session->isWithinScanTime() === false && ! $myAttendance)
        {{-- Di luar jam absensi --}}
        <div class="bg-gray-900 border border-white/5 rounded-xl p-6 mb-6 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-lg font-semibold text-white mb-1">Di Luar Jam Absensi</h2>
            <p class="text-gray-400 text-sm">
                Jam absensi: {{ substr($session->open_time,0,5) }} – {{ substr($session->close_time,0,5) }}
            </p>
            <p class="text-gray-500 text-xs mt-2">Hubungi guru untuk absen manual.</p>
        </div>

    @else
        {{-- Tidak ada sesi --}}
        <div class="bg-gray-900 border border-white/5 rounded-xl p-6 mb-6 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            <h2 class="text-lg font-semibold text-white mb-1">Belum Ada Sesi Hari Ini</h2>
            <p class="text-gray-400 text-sm">Absensi belum dibuka. Coba lagi nanti.</p>
        </div>
    @endif

    {{-- Link riwayat --}}
    <a href="{{ route('siswa.attendance.history') }}"
       class="flex items-center justify-between bg-gray-900 border border-white/5 rounded-xl px-5 py-4 hover:bg-gray-800 transition-colors">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            <span class="text-sm font-medium text-white">Riwayat Absensi</span>
        </div>
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
    </a>

    @if($session && ! $session->is_closed && $session->isWithinScanTime() && ! $myAttendance)
    <script>
    (function() {
        var TOKEN = @json(cache()->get('session_token_' . $session->id));
        var CSRF  = document.querySelector('meta[name="csrf-token"]').content;
        var gps   = null;

        var statusEl = document.getElementById('gps-status');
        var statusText = document.getElementById('gps-status-text');
        var btnAbsen   = document.getElementById('btn-absen');
        var errorEl    = document.getElementById('absen-error');

        function setGpsOk(accuracy) {
            statusEl.className = 'flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-4 py-3 mb-4';
            statusText.textContent = 'Lokasi terdeteksi (\u00b1' + Math.round(accuracy) + 'm) \u2014 siap absen';
            statusText.className   = 'text-sm text-emerald-400';
            btnAbsen.disabled      = false;
        }

        function setGpsError(msg) {
            statusEl.className = 'flex items-center gap-2 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3 mb-4';
            statusText.textContent = msg;
            statusText.className   = 'text-sm text-red-400';
            btnAbsen.disabled      = true;
        }

        // Minta GPS otomatis
        if (!navigator.geolocation) {
            setGpsError('Browser tidak mendukung GPS. Gunakan browser lain.');
        } else {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    gps = { latitude: pos.coords.latitude, longitude: pos.coords.longitude, gps_accuracy: pos.coords.accuracy };
                    setGpsOk(pos.coords.accuracy);
                },
                function(err) {
                    var msgs = { 1: 'Izinkan akses lokasi di browser.', 2: 'GPS tidak tersedia.', 3: 'GPS timeout. Coba lagi.' };
                    setGpsError(msgs[err.code] || 'Gagal deteksi lokasi.');
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }

        btnAbsen.addEventListener('click', async function() {
            if (!gps) { setGpsError('GPS belum terdeteksi. Tunggu sebentar.'); return; }
            if (!TOKEN) { errorEl.textContent = 'Token absensi tidak ditemukan. Hubungi guru.'; errorEl.classList.remove('hidden'); return; }

            this.disabled    = true;
            this.textContent = 'Menyimpan...';

            try {
                var res  = await fetch('{{ route("siswa.attendance.submit") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ token: TOKEN, latitude: gps.latitude, longitude: gps.longitude, gps_accuracy: gps.gps_accuracy }),
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
                errorEl.textContent = 'Koneksi gagal. Periksa internet kamu.';
                errorEl.classList.remove('hidden');
                this.disabled    = false;
                this.textContent = 'Absen Sekarang';
            }
        });
    })();
    </script>
    @endif

</x-simans-layout>
