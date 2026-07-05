<x-simans-layout title="Sesi Absensi">

    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <a href="{{ route('guru.attendance.index') }}"
               class="flex items-center gap-1 text-gray-400 hover:text-white text-sm mb-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-white">{{ $session->classroom->name }}</h1>
            <p class="text-gray-400 text-sm mt-0.5">
                {{ $session->session_date->translatedFormat('l, d F Y') }} ·
                Jam: {{ substr($session->open_time, 0, 5) }}–{{ substr($session->close_time, 0, 5) }}
            </p>
        </div>
        <div class="flex gap-2">
            @if(! $session->is_closed)
                <form method="POST" action="{{ route('guru.attendance.close', $session->id) }}">
                    @csrf @method('PATCH')
                    <button onclick="return confirm('Tutup sesi? Siswa yang belum absen akan ditandai Alfa.')"
                            class="flex items-center gap-2 bg-gray-800 hover:bg-red-900/40 text-gray-400 hover:text-red-400 border border-white/10 hover:border-red-500/30 text-sm font-medium px-4 py-2.5 rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Tutup Sesi
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-300 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Kolom Kiri: QR + Stat ── --}}
        <div class="space-y-4">

            {{-- Panel QR --}}
            <div class="bg-gray-900 border {{ $session->is_closed ? 'border-white/5' : 'border-emerald-500/20' }} rounded-xl p-5">
                <div class="flex items-center gap-2 mb-4">
                    @if($session->is_closed)
                        <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                        <span class="text-sm font-semibold text-gray-400">Sesi Ditutup</span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-sm font-semibold text-emerald-400">Sesi Aktif</span>
                    @endif
                    <span class="ml-auto text-xs text-gray-500">
                        {{ $session->openedBy?->name ?? 'Sistem' }}
                    </span>
                </div>

                @if($qrImage && ! $session->is_closed)
                    <div class="bg-white rounded-xl p-3 w-fit mx-auto mb-3">
                        <div id="qr-container" class="w-48 h-48 flex items-center justify-center">
                            {!! base64_decode($qrImage) !!}
                        </div>
                    </div>
                    <button id="btn-refresh-qr"
                            class="w-full flex items-center justify-center gap-2 bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-300 text-xs font-medium py-2 rounded-xl transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        Perbarui QR
                    </button>
                @else
                    <div class="bg-gray-800 rounded-xl p-6 text-center">
                        <p class="text-gray-500 text-sm">Sesi ditutup</p>
                    </div>
                @endif

                {{-- Info jam --}}
                <div class="grid grid-cols-3 gap-2 mt-4">
                    <div class="bg-gray-800 rounded-xl p-2.5 text-center">
                        <p class="text-xs text-gray-500 mb-0.5">Buka</p>
                        <p class="text-white font-semibold text-sm">{{ substr($session->open_time, 0, 5) }}</p>
                    </div>
                    <div class="bg-amber-900/30 border border-amber-500/20 rounded-xl p-2.5 text-center">
                        <p class="text-xs text-amber-600 mb-0.5">Batas</p>
                        <p class="text-amber-400 font-semibold text-sm">{{ substr($session->late_after, 0, 5) }}</p>
                    </div>
                    <div class="bg-gray-800 rounded-xl p-2.5 text-center">
                        <p class="text-xs text-gray-500 mb-0.5">Tutup</p>
                        <p class="text-white font-semibold text-sm">{{ substr($session->close_time, 0, 5) }}</p>
                    </div>
                </div>
            </div>

            {{-- Stat --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-900 border border-emerald-500/20 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-emerald-400" id="stat-hadir">{{ $recap['hadir'] + $recap['terlambat'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Hadir</p>
                </div>
                <div class="bg-gray-900 border border-white/5 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-gray-400" id="stat-belum">{{ $recap['belum'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Belum</p>
                </div>
                <div class="bg-gray-900 border border-amber-500/20 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-amber-400" id="stat-terlambat">{{ $recap['terlambat'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Terlambat</p>
                </div>
                <div class="bg-gray-900 border border-red-500/20 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-red-400" id="stat-alfa">{{ $recap['alfa'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Alfa</p>
                </div>
            </div>

            {{-- Progress --}}
            <div class="bg-gray-900 border border-white/5 rounded-xl p-4">
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-400">Kehadiran</span>
                    <span class="text-white font-bold" id="rate-text">{{ $recap['rate'] }}%</span>
                </div>
                <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                    <div id="progress-bar" class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                         style="width: {{ $recap['rate'] }}%"></div>
                </div>
                <p class="text-xs text-gray-600 mt-2">{{ $recap['total'] }} siswa terdaftar</p>
            </div>
        </div>

        {{-- ── Kolom Kanan: Tabel Semua Siswa ── --}}
        <div class="lg:col-span-2">
            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                    <h3 class="text-sm font-semibold text-white">Daftar Absensi Siswa</h3>
                    <span class="text-xs text-gray-500">Klik status untuk mengubah</span>
                </div>

                <div class="divide-y divide-white/5">
                    @foreach($session->classroom->students->sortBy('name') as $student)
                        @php
                            $att = $recap['attendances']->firstWhere('student_id', $student->id);
                            $status = $att?->status ?? null;
                        @endphp
                        <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/[0.02] transition-colors">

                            {{-- Avatar --}}
                            <div class="w-9 h-9 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold text-gray-300 flex-shrink-0">
                                {{ substr($student->name, 0, 2) }}
                            </div>

                            {{-- Info siswa --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate">{{ $student->name }}</p>
                                <p class="text-xs text-gray-500">
                                    NIS: {{ $student->nis }}
                                    @if($att && $att->scanned_at)
                                        · {{ $att->scanned_at->format('H:i:s') }}
                                    @endif
                                    @if($att && $att->is_manual_entry)
                                        · <span class="text-amber-500">Manual</span>
                                    @endif
                                </p>
                            </div>

                            {{-- Status badge / tombol edit --}}
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($status)
                                    @php
                                        $colors = [
                                            'hadir'     => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'terlambat' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            'izin'      => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                            'sakit'     => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                            'alfa'      => 'bg-red-500/10 text-red-400 border-red-500/20',
                                        ];
                                        $labels = ['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alfa' => 'Alfa'];
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $colors[$status] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/20' }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ $labels[$status] ?? $status }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border bg-gray-800 text-gray-500 border-white/5">
                                        Belum Absen
                                    </span>
                                @endif

                                {{-- Tombol edit --}}
                                <button type="button"
                                        onclick="openModal({{ $student->id }}, '{{ addslashes($student->name) }}', '{{ $status ?? '' }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-400 hover:text-white transition-colors"
                                        title="Edit status absen">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── Modal Edit Status ── --}}
    <div id="modal-edit"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4"
         onclick="if(event.target===this) closeModal()">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-sm">

            <div class="flex items-center justify-between p-5 border-b border-white/5">
                <h3 class="font-semibold text-white">Edit Status Absensi</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Siswa</p>
                    <p class="text-sm font-semibold text-white" id="modal-student-name"></p>
                </div>

                <div>
                    <p class="text-xs text-gray-400 mb-2">Status Kehadiran</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach([
                            'hadir'     => ['Hadir',     'emerald'],
                            'terlambat' => ['Terlambat', 'amber'],
                            'izin'      => ['Izin',      'blue'],
                            'sakit'     => ['Sakit',     'purple'],
                            'alfa'      => ['Alfa',      'red'],
                        ] as $val => [$label, $color])
                        <button type="button"
                                onclick="selectStatus('{{ $val }}', '{{ $color }}', this)"
                                data-value="{{ $val }}"
                                data-color="{{ $color }}"
                                class="status-btn py-2 px-2 rounded-xl border border-white/10 text-gray-400 text-xs font-semibold transition-all hover:border-{{ $color }}-500 hover:text-{{ $color }}-400">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">
                        Alasan / Keterangan <span class="text-red-400">*</span>
                    </label>
                    <textarea id="modal-reason" rows="3"
                              class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 resize-none transition-colors"
                              placeholder="Wajib diisi. Dicatat dalam audit log..."></textarea>
                    <p class="text-xs text-gray-600 mt-1">Catatan ini permanen dan tidak bisa dihapus.</p>
                </div>
            </div>

            <div class="p-5 border-t border-white/5 flex gap-3">
                <button onclick="closeModal()"
                        class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2.5 rounded-xl border border-white/10 transition-colors">
                    Batal
                </button>
                <button id="btn-submit-edit"
                        onclick="submitEdit()"
                        class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                    Simpan
                </button>
            </div>
        </div>
    </div>

</x-simans-layout>

@push('scripts')
<script>
(function() {
    'use strict';

    const SESSION_ID = {{ $session->id }};
    const IS_ACTIVE  = {{ $session->isActive() ? 'true' : 'false' }};
    const CSRF       = document.querySelector('meta[name="csrf-token"]').content;

    // ── Variabel modal ────────────────────────────────────────────────────
    let activeStudentId   = null;
    let activeStatusValue = null;

    // ── Buka modal ────────────────────────────────────────────────────────
    window.openModal = function(studentId, studentName, currentStatus) {
        activeStudentId   = studentId;
        activeStatusValue = currentStatus || null;

        document.getElementById('modal-student-name').textContent = studentName;
        document.getElementById('modal-reason').value = '';

        // Reset semua tombol status
        document.querySelectorAll('.status-btn').forEach(btn => {
            const c = btn.dataset.color;
            btn.className = btn.className
                .replace(new RegExp(`border-${c}-500`, 'g'), 'border-white/10')
                .replace(new RegExp(`text-${c}-400`, 'g'), 'text-gray-400');
        });

        // Highlight status saat ini
        if (currentStatus) {
            const btn = document.querySelector(`.status-btn[data-value="${currentStatus}"]`);
            if (btn) selectStatus(currentStatus, btn.dataset.color, btn);
        }

        const modal = document.getElementById('modal-edit');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    // ── Tutup modal ───────────────────────────────────────────────────────
    window.closeModal = function() {
        const modal = document.getElementById('modal-edit');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        activeStudentId   = null;
        activeStatusValue = null;
    };

    // ── Pilih status ──────────────────────────────────────────────────────
    window.selectStatus = function(value, color, btn) {
        // Reset semua
        document.querySelectorAll('.status-btn').forEach(b => {
            const c = b.dataset.color;
            b.classList.remove(`border-${c}-500`, `text-${c}-400`);
            b.classList.add('border-white/10', 'text-gray-400');
        });

        // Aktifkan yang dipilih
        btn.classList.remove('border-white/10', 'text-gray-400');
        btn.classList.add(`border-${color}-500`, `text-${color}-400`);

        activeStatusValue = value;
    };

    // ── Submit edit ───────────────────────────────────────────────────────
    window.submitEdit = async function() {
        if (!activeStudentId) {
            alert('Data siswa tidak ditemukan.');
            return;
        }
        if (!activeStatusValue) {
            alert('Pilih status kehadiran terlebih dahulu.');
            return;
        }

        const reason = document.getElementById('modal-reason').value.trim();
        if (!reason) {
            alert('Alasan wajib diisi.');
            return;
        }

        const btn = document.getElementById('btn-submit-edit');
        btn.disabled    = true;
        btn.textContent = 'Menyimpan...';

        try {
            const res = await fetch(`/guru/absensi/sesi/${SESSION_ID}/manual`, {
                method:  'POST',
                headers: {
                    'Content-Type':  'application/json',
                    'X-CSRF-TOKEN':  CSRF,
                },
                body: JSON.stringify({
                    student_id: activeStudentId,
                    status:     activeStatusValue,
                    reason:     reason,
                }),
            });

            const data = await res.json();

            if (data.success) {
                closeModal();
                location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan. Coba lagi.');
            }

        } catch (e) {
            alert('Koneksi gagal. Periksa internet kamu.');
        }

        btn.disabled    = false;
        btn.textContent = 'Simpan';
    };

    // ── Refresh QR ────────────────────────────────────────────────────────
    document.getElementById('btn-refresh-qr')?.addEventListener('click', async function() {
        this.disabled    = true;
        this.textContent = 'Memperbarui...';

        try {
            const res  = await fetch(`/guru/absensi/sesi/${SESSION_ID}/refresh-qr`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const data = await res.json();

            if (data.success) {
                document.getElementById('qr-container').innerHTML = atob(data.qr_image);
            }
        } catch (e) {}

        this.disabled    = false;
        this.textContent = 'Perbarui QR';
    });

    // ── Polling rekap setiap 5 detik ─────────────────────────────────────
    if (IS_ACTIVE) {
        setInterval(async function() {
            try {
                const res  = await fetch(`/guru/absensi/sesi/${SESSION_ID}/rekap`);
                const data = await res.json();
                if (!data.success) return;

                const r  = data.recap;
                const el = id => document.getElementById(id);

                if (el('stat-hadir'))     el('stat-hadir').textContent     = r.hadir + r.terlambat;
                if (el('stat-terlambat')) el('stat-terlambat').textContent  = r.terlambat;
                if (el('stat-belum'))     el('stat-belum').textContent      = r.belum;
                if (el('stat-alfa'))      el('stat-alfa').textContent       = r.alfa;
                if (el('rate-text'))      el('rate-text').textContent       = r.rate + '%';
                if (el('progress-bar'))   el('progress-bar').style.width    = r.rate + '%';

            } catch (e) {}
        }, 5000);
    }

})();
</script>
@endpush