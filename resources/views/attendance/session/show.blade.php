<x-simans-layout title="Sesi Absensi Aktif">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('guru.attendance.index') }}" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-white">{{ $session->subject->name }}</h1>
            <p class="text-gray-400 text-sm">{{ $session->classroom->name }} — {{ $session->session_date->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="flex gap-2">
            @if(! $session->roll_call_done)
            <form method="POST" action="{{ route('guru.attendance.roll-call', $session->id) }}">
                @csrf
                <button type="submit"
                        onclick="return confirm('Roll call selesai? Siswa yang belum absen akan ditandai Alfa.')"
                        class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Roll Call Selesai
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('guru.attendance.close', $session->id) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        onclick="return confirm('Tutup sesi absensi ini?')"
                        class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium px-4 py-2.5 rounded-xl border border-white/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Tutup Sesi
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-300 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Panel QR --}}
        <div class="bg-gray-900 border border-emerald-500/20 border-t-2 border-t-emerald-500 rounded-xl p-6">
            <div class="flex items-center gap-2 mb-4">
                @if($session->isActive())
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-sm font-semibold text-emerald-400">Sesi Aktif</span>
                    <span class="ml-auto text-sm font-mono text-amber-400" id="countdown"
                          data-expires="{{ $session->token_expires_at->toISOString() }}">--:--</span>
                @else
                    <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                    <span class="text-sm font-semibold text-gray-500">Sesi Ditutup</span>
                @endif
            </div>

            @if($qrImage)
                <div class="bg-white rounded-2xl p-4 w-fit mx-auto mb-4">
                    <div id="qr-image" class="w-56 h-56">{!! base64_decode($qrImage) !!}</div>
                </div>
                <p class="text-center text-xs text-gray-500 mb-4">Tampilkan QR ini ke siswa untuk di-scan</p>
            @else
                <div class="bg-gray-800 rounded-2xl p-8 flex items-center justify-center mb-4">
                    <p class="text-gray-500 text-sm">QR tidak tersedia — sesi sudah ditutup</p>
                </div>
            @endif

            @if($session->isActive())
                <div class="flex items-center gap-3">
                    <select id="qr-duration" class="flex-1 bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500">
                        <option value="5">5 menit</option>
                        <option value="10" selected>10 menit</option>
                        <option value="15">15 menit</option>
                        <option value="30">30 menit</option>
                    </select>
                    <button id="btn-refresh" class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-300 text-sm font-medium px-4 py-2 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        Perbarui QR
                    </button>
                </div>
            @endif
        </div>

        {{-- Rekap --}}
        <div class="space-y-4">

            {{-- Stat mini --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-gray-900 border border-white/5 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-emerald-400" id="stat-hadir">{{ $recap['hadir'] + $recap['terlambat'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Hadir</p>
                </div>
                <div class="bg-gray-900 border border-white/5 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-amber-400" id="stat-belum">{{ $recap['belum'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Belum</p>
                </div>
                <div class="bg-gray-900 border border-white/5 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-red-400" id="stat-alfa">{{ $recap['alfa'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Alfa</p>
                </div>
            </div>

            {{-- Progress --}}
            <div class="bg-gray-900 border border-white/5 rounded-xl p-4">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-400">Kehadiran</span>
                    <span class="text-white font-semibold" id="rate">{{ $recap['rate'] }}%</span>
                </div>
                <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                         id="progress"
                         style="width: {{ $recap['rate'] }}%"></div>
                </div>
                <p class="text-xs text-gray-600 mt-2">{{ $recap['total'] }} siswa terdaftar</p>
            </div>

            {{-- Daftar hadir --}}
            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-white/5 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-white">Sudah Absen</h3>
                    <span class="text-xs text-gray-500" id="present-count">{{ $recap['hadir'] + $recap['terlambat'] }} siswa</span>
                </div>
                <div class="divide-y divide-white/5 max-h-48 overflow-y-auto" id="attendance-list">
                    @forelse($recap['attendances'] as $att)
                        <div class="flex items-center gap-3 px-4 py-3">
                            <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-xs font-bold text-gray-300 flex-shrink-0">
                                {{ substr($att->student->name, 0, 2) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-white truncate">{{ $att->student->name }}</p>
                                <p class="text-xs text-gray-500">{{ $att->scanned_at->format('H:i:s') }}</p>
                            </div>
                            <x-status-badge :status="$att->status"/>
                            <button class="text-gray-600 hover:text-gray-400 transition-colors"
                                    data-action="override"
                                    data-student-id="{{ $att->student_id }}"
                                    data-student-name="{{ $att->student->name }}"
                                    data-current="{{ $att->status }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                </svg>
                            </button>
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center">
                            <p class="text-gray-600 text-sm">Menunggu siswa scan QR...</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Belum absen --}}
            @if($recap['belum'] > 0)
            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-white/5">
                    <h3 class="text-sm font-semibold text-white">Belum Absen</h3>
                </div>
                <div class="divide-y divide-white/5 max-h-36 overflow-y-auto">
                    @foreach($recap['missing'] as $student)
                        <div class="flex items-center gap-3 px-4 py-3">
                            <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold text-gray-500 flex-shrink-0">
                                {{ substr($student->name, 0, 2) }}
                            </div>
                            <span class="flex-1 text-sm text-gray-400 truncate">{{ $student->name }}</span>
                            <button class="text-xs text-emerald-400 hover:text-emerald-300 font-medium transition-colors"
                                    data-action="override"
                                    data-student-id="{{ $student->id }}"
                                    data-student-name="{{ $student->name }}"
                                    data-current="">
                                Input Manual
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Modal Koreksi --}}
    <div id="modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70">
        <div class="bg-gray-900 border border-white/10 rounded-2xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">Koreksi Absensi</h3>
                <button id="close-modal" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-gray-400 text-sm mb-4" id="modal-student-name"></p>

            <div class="grid grid-cols-3 gap-2 mb-4" id="status-options">
                @foreach(['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alfa' => 'Alfa'] as $val => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="new_status" value="{{ $val }}" class="sr-only">
                    <span class="block text-center text-xs font-semibold py-2 px-3 rounded-lg border border-white/10 text-gray-400 hover:border-emerald-500 hover:text-emerald-400 transition-all peer-checked:border-emerald-500 status-btn" data-value="{{ $val }}">
                        {{ $label }}
                    </span>
                </label>
                @endforeach
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Alasan Koreksi <span class="text-red-400">*</span></label>
                <textarea id="override-reason" rows="3" placeholder="Wajib diisi — tercatat di audit log..."
                          class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 resize-none transition-colors"></textarea>
            </div>

            <div class="flex gap-3">
                <button id="cancel-modal" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2.5 rounded-xl border border-white/10 transition-colors">
                    Batal
                </button>
                <button id="submit-override" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</x-simans-layout>

@push('scripts')
<script>
(function() {
    const SESSION_ID = {{ $session->id }};
    const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
    const isActive   = {{ $session->isActive() ? 'true' : 'false' }};

    // Countdown
    const countdownEl = document.getElementById('countdown');
    if (countdownEl && isActive) {
        const expires = new Date(countdownEl.dataset.expires);
        const tick = () => {
            const diff = Math.max(0, Math.floor((expires - Date.now()) / 1000));
            const m = String(Math.floor(diff / 60)).padStart(2, '0');
            const s = String(diff % 60).padStart(2, '0');
            countdownEl.textContent = m + ':' + s;
            if (diff === 0) countdownEl.classList.add('text-red-400');
        };
        tick();
        setInterval(tick, 1000);
    }

    // Refresh QR
    document.getElementById('btn-refresh')?.addEventListener('click', async function() {
        const duration = document.getElementById('qr-duration').value;
        this.disabled  = true;
        try {
            const res  = await fetch(`/guru/absensi/sesi/${SESSION_ID}/refresh-qr`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ duration_minutes: parseInt(duration) }),
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('qr-image').innerHTML = atob(data.qr_image);
            }
        } catch(e) {}
        this.disabled = false;
    });

    // Polling rekap setiap 5 detik
    if (isActive) {
        setInterval(async () => {
            try {
                const res  = await fetch(`/guru/absensi/sesi/${SESSION_ID}/rekap`);
                const data = await res.json();
                if (!data.success) return;
                const r = data.recap;
                document.getElementById('stat-hadir').textContent = r.hadir + r.terlambat;
                document.getElementById('stat-belum').textContent = r.belum;
                document.getElementById('stat-alfa').textContent  = r.alfa;
                document.getElementById('rate').textContent       = r.rate + '%';
                document.getElementById('progress').style.width   = r.rate + '%';
            } catch(e) {}
        }, 5000);
    }

    // Modal koreksi
    const modal       = document.getElementById('modal');
    const reasonEl    = document.getElementById('override-reason');
    let   activeId    = null;
    let   activeStatus = null;

    function openModal(studentId, studentName, currentStatus) {
        activeId     = studentId;
        activeStatus = currentStatus;
        document.getElementById('modal-student-name').textContent = studentName;
        reasonEl.value = '';
        document.querySelectorAll('.status-btn').forEach(btn => {
            btn.classList.toggle('border-emerald-500', btn.dataset.value === currentStatus);
            btn.classList.toggle('text-emerald-400', btn.dataset.value === currentStatus);
        });
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        activeId = null;
    }

    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.status-btn').forEach(b => {
                b.classList.remove('border-emerald-500', 'text-emerald-400');
            });
            btn.classList.add('border-emerald-500', 'text-emerald-400');
            activeStatus = btn.dataset.value;
        });
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-action="override"]');
        if (btn) openModal(btn.dataset.studentId, btn.dataset.studentName, btn.dataset.current);
    });

    document.getElementById('close-modal')?.addEventListener('click', closeModal);
    document.getElementById('cancel-modal')?.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    document.getElementById('submit-override')?.addEventListener('click', async function() {
        if (!activeId)     { alert('Pilih siswa terlebih dahulu.'); return; }
        if (!activeStatus) { alert('Pilih status kehadiran.'); return; }
        const reason = reasonEl.value.trim();
        if (!reason)       { alert('Alasan koreksi wajib diisi.'); return; }

        this.disabled    = true;
        this.textContent = 'Menyimpan...';

        try {
            const res  = await fetch(`/guru/absensi/sesi/${SESSION_ID}/koreksi`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ student_id: activeId, new_status: activeStatus, reason }),
            });
            const data = await res.json();
            if (data.success) {
                closeModal();
                location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan koreksi.');
            }
        } catch(e) { alert('Koneksi gagal.'); }

        this.disabled    = false;
        this.textContent = 'Simpan';
    });
})();
</script>
@endpush