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
                Jam scan: {{ substr($session->open_time, 0, 5) }}–{{ substr($session->close_time, 0, 5) }}
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

    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-900/30 border border-red-700/40 text-red-300 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ── Panel QR ── --}}
        <div class="bg-gray-900 border {{ $session->is_closed ? 'border-white/5' : 'border-emerald-500/20' }} rounded-xl p-6">

            <div class="flex items-center gap-2 mb-5">
                @if($session->is_closed)
                    <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                    <span class="text-sm font-semibold text-gray-400">Sesi Ditutup</span>
                @else
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-sm font-semibold text-emerald-400">Sesi Aktif</span>
                    <span class="ml-auto text-xs text-gray-400">
                        Dibuka oleh {{ $session->openedBy->name }}
                    </span>
                @endif
            </div>

            {{-- QR Image --}}
            @if($qrImage && ! $session->is_closed)
                <div class="bg-white rounded-2xl p-4 w-fit mx-auto mb-4">
                    <div id="qr-container" class="w-64 h-64 flex items-center justify-center">
                        {!! base64_decode($qrImage) !!}
                    </div>
                </div>
                <p class="text-center text-xs text-gray-500 mb-5">
                    Tampilkan QR ini di layar kelas — siswa scan dari HP masing-masing
                </p>
                <button id="btn-refresh-qr"
                        class="w-full flex items-center justify-center gap-2 bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-300 text-sm font-medium py-2.5 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    Perbarui QR Code
                </button>
            @else
                <div class="bg-gray-800 rounded-2xl p-8 text-center mb-4">
                    <svg class="w-12 h-12 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5z"/>
                    </svg>
                    <p class="text-gray-500 text-sm">Sesi sudah ditutup</p>
                </div>
            @endif

            {{-- Info jam --}}
            <div class="grid grid-cols-3 gap-2 mt-4">
                <div class="bg-gray-800 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-500 mb-1">Buka</p>
                    <p class="text-white font-semibold text-sm">{{ substr($session->open_time, 0, 5) }}</p>
                </div>
                <div class="bg-amber-900/30 border border-amber-500/20 rounded-xl p-3 text-center">
                    <p class="text-xs text-amber-600 mb-1">Batas</p>
                    <p class="text-amber-400 font-semibold text-sm">{{ substr($session->late_after, 0, 5) }}</p>
                </div>
                <div class="bg-gray-800 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-500 mb-1">Tutup</p>
                    <p class="text-white font-semibold text-sm">{{ substr($session->close_time, 0, 5) }}</p>
                </div>
            </div>
        </div>

        {{-- ── Panel Rekap ── --}}
        <div class="space-y-4">

            {{-- Stat cards --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-900 border border-emerald-500/20 rounded-xl p-4">
                    <p class="text-3xl font-bold text-emerald-400" id="stat-hadir">{{ $recap['hadir'] + $recap['terlambat'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Hadir</p>
                </div>
                <div class="bg-gray-900 border border-white/5 rounded-xl p-4">
                    <p class="text-3xl font-bold text-gray-400" id="stat-belum">{{ $recap['belum'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Belum Absen</p>
                </div>
                <div class="bg-gray-900 border border-amber-500/20 rounded-xl p-4">
                    <p class="text-3xl font-bold text-amber-400" id="stat-terlambat">{{ $recap['terlambat'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Terlambat</p>
                </div>
                <div class="bg-gray-900 border border-red-500/20 rounded-xl p-4">
                    <p class="text-3xl font-bold text-red-400" id="stat-alfa">{{ $recap['alfa'] }}</p>
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

            {{-- Daftar siswa hadir --}}
            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
                    <h3 class="text-sm font-semibold text-white">Sudah Absen</h3>
                    <span class="text-xs text-gray-500" id="attended-count">
                        {{ $recap['hadir'] + $recap['terlambat'] + $recap['izin'] + $recap['sakit'] }} siswa
                    </span>
                </div>
                <div class="divide-y divide-white/5 max-h-52 overflow-y-auto" id="attendance-list">
                    @forelse($recap['attendances'] as $att)
                        <div class="flex items-center gap-3 px-4 py-3" id="row-{{ $att->student_id }}">
                            <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold text-gray-300 flex-shrink-0">
                                {{ substr($att->student->name, 0, 2) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-white truncate">{{ $att->student->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $att->scanned_at ? $att->scanned_at->format('H:i:s') : 'Manual' }}
                                    @if($att->is_manual_entry)
                                        <span class="text-amber-500"> · Manual</span>
                                    @endif
                                </p>
                            </div>
                            <x-status-badge :status="$att->status"/>
                            <button class="text-gray-600 hover:text-white transition-colors p-1"
                                    data-action="edit"
                                    data-student-id="{{ $att->student_id }}"
                                    data-student-name="{{ $att->student->name }}"
                                    data-current="{{ $att->status }}"
                                    title="Koreksi">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                </svg>
                            </button>
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center" id="empty-state">
                            <p class="text-gray-600 text-sm">Menunggu siswa scan QR...</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Siswa belum absen --}}
            @if($recap['belum'] > 0 || $recap['missing']->count() > 0)
            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
                    <h3 class="text-sm font-semibold text-white">Belum Absen</h3>
                    <span class="text-xs text-red-400" id="missing-count">{{ $recap['belum'] }} siswa</span>
                </div>
                <div class="divide-y divide-white/5 max-h-44 overflow-y-auto">
                    @foreach($recap['missing'] as $student)
                        <div class="flex items-center gap-3 px-4 py-3">
                            <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold text-gray-600 flex-shrink-0">
                                {{ substr($student->name, 0, 2) }}
                            </div>
                            <span class="flex-1 text-sm text-gray-400 truncate">{{ $student->name }}</span>
                            <button class="text-xs text-emerald-400 hover:text-emerald-300 font-medium transition-colors py-1 px-2 rounded-lg hover:bg-emerald-500/10"
                                    data-action="edit"
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

    {{-- ── Roll Call Section ── --}}
    @if(! $session->is_closed)
    <div class="mt-6 bg-gray-900 border border-amber-500/20 rounded-xl p-5">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Validasi Kehadiran (Roll Call)</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Panggil nama siswa untuk konfirmasi kehadiran fisik.
                    Siswa yang tidak merespon akan ditandai Alfa.
                    @if($session->roll_call_done)
                        <span class="text-emerald-400 font-medium">· Sudah dilakukan pukul {{ $session->roll_call_at->format('H:i') }}</span>
                    @endif
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('guru.attendance.roll-call', $session->id) }}" id="roll-call-form">
            @csrf

            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Mata Pelajaran (opsional)</label>
                    <input type="text" name="subject_name" placeholder="cth: Matematika"
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Catatan (opsional)</label>
                    <input type="text" name="notes" placeholder="cth: Semua siswa aktif mengikuti pelajaran"
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
            </div>

            {{-- Daftar semua siswa dengan checkbox --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 mb-4" id="rollcall-list">
                @foreach($session->classroom->students->sortBy('name') as $student)
                    @php
                        $att = $recap['attendances']->firstWhere('student_id', $student->id);
                        $isPresent = $att && in_array($att->status, ['hadir', 'terlambat', 'izin', 'sakit']);
                    @endphp
                    <label class="flex items-center gap-2 bg-gray-800 border {{ $isPresent ? 'border-emerald-500/30' : 'border-white/5' }} rounded-xl px-3 py-2.5 cursor-pointer hover:border-emerald-500/50 transition-all">
                        <input type="checkbox"
                               name="present_ids[]"
                               value="{{ $student->id }}"
                               class="rounded border-gray-600 text-emerald-500 focus:ring-emerald-500"
                               {{ $isPresent ? 'checked' : '' }}>
                        <span class="text-sm text-white truncate">{{ $student->name }}</span>
                    </label>
                @endforeach
            </div>

            <div class="flex gap-3">
                <button type="button" id="btn-check-all"
                        class="text-xs text-gray-400 hover:text-white transition-colors py-1 px-3 rounded-lg bg-gray-800 border border-white/10">
                    Centang Semua
                </button>
                <button type="button" id="btn-uncheck-all"
                        class="text-xs text-gray-400 hover:text-white transition-colors py-1 px-3 rounded-lg bg-gray-800 border border-white/10">
                    Hapus Semua
                </button>
                <button type="submit"
                        class="ml-auto flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-5 py-2 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Simpan Roll Call
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- ── Modal Input Manual ── --}}
    <div id="modal-manual" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-md">
            <div class="flex items-center justify-between p-5 border-b border-white/5">
                <h3 class="font-semibold text-white">Input Absensi Manual</h3>
                <button id="close-modal" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <p class="text-sm text-gray-300">Siswa: <span id="modal-student-name" class="font-semibold text-white"></span></p>

                <div>
                    <label class="block text-xs text-gray-400 mb-2">Status Kehadiran</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['hadir' => ['Hadir','emerald'], 'terlambat' => ['Terlambat','amber'], 'izin' => ['Izin','blue'], 'sakit' => ['Sakit','purple'], 'alfa' => ['Alfa','red']] as $val => [$label, $color])
                        <label class="cursor-pointer">
                            <input type="radio" name="modal_status" value="{{ $val }}" class="sr-only status-radio">
                            <span class="status-opt block text-center text-xs font-semibold py-2 px-2 rounded-xl border border-white/10 text-gray-400 transition-all hover:border-{{ $color }}-500 hover:text-{{ $color }}-400 cursor-pointer"
                                  data-value="{{ $val }}" data-color="{{ $color }}">
                                {{ $label }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Alasan / Keterangan <span class="text-red-400">*</span></label>
                    <textarea id="modal-reason" rows="3"
                              class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 resize-none transition-colors"
                              placeholder="Wajib diisi. Dicatat dalam audit log..."></textarea>
                </div>
            </div>
            <div class="p-5 border-t border-white/5 flex gap-3">
                <button id="cancel-modal" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2.5 rounded-xl border border-white/10 transition-colors">
                    Batal
                </button>
                <button id="submit-manual" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
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

    // ── Refresh QR ────────────────────────────────────────────────────────
    document.getElementById('btn-refresh-qr')?.addEventListener('click', async function() {
        this.disabled    = true;
        this.textContent = 'Memperbarui...';
        try {
            const res  = await fetch(`/guru/absensi/sesi/${SESSION_ID}/refresh-qr`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('qr-container').innerHTML = atob(data.qr_image);
            }
        } catch(e) {}
        this.disabled    = false;
        this.innerHTML   = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg> Perbarui QR Code';
    });

    // ── Polling rekap setiap 5 detik ─────────────────────────────────────
    if (IS_ACTIVE) {
        setInterval(async function() {
            try {
                const res  = await fetch(`/guru/absensi/sesi/${SESSION_ID}/rekap`);
                const data = await res.json();
                if (!data.success) return;
                const r = data.recap;
                const el = (id) => document.getElementById(id);
                if (el('stat-hadir'))    el('stat-hadir').textContent    = r.hadir + r.terlambat;
                if (el('stat-terlambat'))el('stat-terlambat').textContent = r.terlambat;
                if (el('stat-belum'))    el('stat-belum').textContent    = r.belum;
                if (el('stat-alfa'))     el('stat-alfa').textContent     = r.alfa;
                if (el('rate-text'))     el('rate-text').textContent     = r.rate + '%';
                if (el('progress-bar'))  el('progress-bar').style.width  = r.rate + '%';
            } catch(e) {}
        }, 5000);
    }

    // ── Roll call helpers ─────────────────────────────────────────────────
    document.getElementById('btn-check-all')?.addEventListener('click', function() {
        document.querySelectorAll('#rollcall-list input[type="checkbox"]').forEach(c => c.checked = true);
    });
    document.getElementById('btn-uncheck-all')?.addEventListener('click', function() {
        document.querySelectorAll('#rollcall-list input[type="checkbox"]').forEach(c => c.checked = false);
    });

    // Saat submit roll call, tambahkan absent_ids secara otomatis
    document.getElementById('roll-call-form')?.addEventListener('submit', function(e) {
        // Hapus hidden input lama
        this.querySelectorAll('input[name="absent_ids[]"]').forEach(el => el.remove());

        const allCheckboxes = this.querySelectorAll('input[name="present_ids[]"]');
        allCheckboxes.forEach(cb => {
            if (!cb.checked) {
                const input    = document.createElement('input');
                input.type     = 'hidden';
                input.name     = 'absent_ids[]';
                input.value    = cb.value;
                this.appendChild(input);
            }
        });
    });

    // ── Modal Input Manual ────────────────────────────────────────────────
    const modal        = document.getElementById('modal-manual');
    const modalName    = document.getElementById('modal-student-name');
    const modalReason  = document.getElementById('modal-reason');
    let   activeStudentId   = null;
    let   activeStatusValue = null;

    function openModal(studentId, studentName, currentStatus) {
        activeStudentId   = studentId;
        activeStatusValue = currentStatus || null;
        modalName.textContent = studentName;
        modalReason.value     = '';

        // Reset semua pilihan
        document.querySelectorAll('.status-opt').forEach(el => {
            el.className = el.className.replace(/border-\w+-500 text-\w+-400/g, '').trim();
            el.classList.add('border-white/10', 'text-gray-400');
        });

        // Set pilihan current
        if (currentStatus) {
            const target = document.querySelector(`.status-opt[data-value="${currentStatus}"]`);
            if (target) selectStatus(target);
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function selectStatus(el) {
        document.querySelectorAll('.status-opt').forEach(opt => {
            opt.classList.remove(...['border-emerald-500', 'text-emerald-400', 'border-amber-500',
                'text-amber-400', 'border-blue-500', 'text-blue-400', 'border-purple-500',
                'text-purple-400', 'border-red-500', 'text-red-400']);
            opt.classList.add('border-white/10', 'text-gray-400');
        });
        const color = el.dataset.color;
        el.classList.remove('border-white/10', 'text-gray-400');
        el.classList.add(`border-${color}-500`, `text-${color}-400`);
        activeStatusValue = el.dataset.value;
    }

    document.querySelectorAll('.status-opt').forEach(el => {
        el.addEventListener('click', () => selectStatus(el));
    });

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        activeStudentId   = null;
        activeStatusValue = null;
    }

    document.getElementById('close-modal')?.addEventListener('click', closeModal);
    document.getElementById('cancel-modal')?.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-action="edit"]');
        if (btn) openModal(btn.dataset.studentId, btn.dataset.studentName, btn.dataset.current);
    });

    document.getElementById('submit-manual')?.addEventListener('click', async function() {
        if (!activeStudentId)   { alert('Pilih siswa.'); return; }
        if (!activeStatusValue) { alert('Pilih status.'); return; }
        const reason = modalReason.value.trim();
        if (!reason)            { alert('Alasan wajib diisi.'); return; }

        this.disabled    = true;
        this.textContent = 'Menyimpan...';

        try {
            const res  = await fetch(`/guru/absensi/sesi/${SESSION_ID}/manual`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
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
                alert(data.message || 'Gagal menyimpan.');
            }
        } catch(e) { alert('Koneksi gagal.'); }

        this.disabled    = false;
        this.textContent = 'Simpan';
    });

})();
</script>
@endpush