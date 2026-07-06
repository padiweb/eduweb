<x-simans-layout title="Sesi Absensi">

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
                {{ $session->session_date->translatedFormat('l, d F Y') }} &middot;
                Jam: {{ substr($session->open_time, 0, 5) }}&ndash;{{ substr($session->close_time, 0, 5) }}
            </p>
        </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri: QR + Stat --}}
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
                    <span class="ml-auto text-xs text-gray-500">{{ $session->openedBy?->name ?? 'Sistem' }}</span>
                </div>

                @if($qrImage && ! $session->is_closed)
                    <div class="bg-white rounded-xl p-3 w-fit mx-auto mb-3">
                        <div id="qr-container" class="w-48 h-48 flex items-center justify-center">
                            {!! base64_decode($qrImage) !!}
                        </div>
                    </div>
                    <p class="text-center text-xs text-gray-600">QR diperbarui otomatis setiap hari</p>
                @else
                    <div class="bg-gray-800 rounded-xl p-6 text-center">
                        <p class="text-gray-500 text-sm">Sesi ditutup</p>
                    </div>
                @endif

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

            {{-- Stat cards --}}
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

        {{-- Kolom Kanan: Tabel siswa --}}
        <div class="lg:col-span-2">
            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">

                {{-- Toolbar --}}
                <div class="flex flex-wrap items-center gap-2 px-5 py-4 border-b border-white/5">
                    <h3 class="text-sm font-semibold text-white mr-auto">Daftar Absensi Siswa</h3>
                    @if(! $session->is_closed)
                        <button type="button" id="btn-check-all"
                                class="text-xs text-gray-400 hover:text-white py-1.5 px-3 rounded-lg bg-gray-800 border border-white/10 transition-colors">
                            Centang Semua
                        </button>
                        <button type="button" id="btn-uncheck-all"
                                class="text-xs text-gray-400 hover:text-white py-1.5 px-3 rounded-lg bg-gray-800 border border-white/10 transition-colors">
                            Hapus Semua
                        </button>
                        <button type="button" id="btn-bulk-edit"
                                class="flex items-center gap-1.5 text-xs text-amber-400 hover:text-amber-300 py-1.5 px-3 rounded-lg bg-amber-500/10 border border-amber-500/20 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                            </svg>
                            Edit Massal
                        </button>
                    @endif
                </div>

                <form method="POST" action="{{ route('guru.attendance.roll-call', $session->id) }}" id="roll-call-form">
                    @csrf
                    <input type="hidden" name="subject_name" value="">
                    <input type="hidden" name="notes" value="">

                    <div class="divide-y divide-white/5">
                        @foreach($session->classroom->students->sortBy('name') as $student)
                            @php
                                $att      = $recap['attendances']->firstWhere('student_id', $student->id);
                                $status   = $att?->status ?? null;
                                $colorMap = [
                                    'hadir'     => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'terlambat' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    'izin'      => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    'sakit'     => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                    'alfa'      => 'bg-red-500/10 text-red-400 border-red-500/20',
                                ];
                                $labelMap  = ['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alfa'=>'Alfa'];
                                $isPresent = $status && in_array($status, ['hadir','terlambat','izin','sakit']);
                            @endphp
                            <div class="flex items-center gap-3 px-5 py-3 hover:bg-white/[0.02] transition-colors">

                                @if(! $session->is_closed)
                                    <input type="checkbox"
                                           name="present_ids[]"
                                           value="{{ $student->id }}"
                                           {{ $isPresent ? 'checked' : '' }}
                                           class="roll-call-cb w-4 h-4 rounded border-gray-600 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0 flex-shrink-0 cursor-pointer">
                                @endif

                                <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold text-gray-300 flex-shrink-0">
                                    {{ substr($student->name, 0, 2) }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate">{{ $student->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        NIS: {{ $student->nis }}
                                        @if($att && $att->scanned_at) &middot; {{ $att->scanned_at->format('H:i:s') }} @endif
                                        @if($att && $att->is_manual_entry) &middot; <span class="text-amber-500">Manual</span> @endif
                                    </p>
                                </div>

                                @if($status)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border flex-shrink-0 {{ $colorMap[$status] ?? 'bg-gray-800 text-gray-400 border-white/10' }}">
                                        {{ $labelMap[$status] ?? $status }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-gray-800 text-gray-500 border-white/5 flex-shrink-0">
                                        Belum Absen
                                    </span>
                                @endif

                                <button type="button"
                                        class="btn-edit w-8 h-8 flex items-center justify-center rounded-lg bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-400 hover:text-white transition-colors flex-shrink-0"
                                        data-student-id="{{ $student->id }}"
                                        data-student-name="{{ $student->name }}"
                                        data-current-status="{{ $status ?? '' }}">
                                    <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    @if(! $session->is_closed)
                        <div class="px-5 py-4 border-t border-white/5 bg-gray-800/50 flex items-center justify-between gap-4">
                            <p class="text-xs text-gray-400 flex-1">
                                Centang siswa yang hadir fisik, lalu simpan roll call.
                                @if($session->roll_call_done)
                                    <span class="text-emerald-400 font-medium ml-1">Sudah pukul {{ $session->roll_call_at->format('H:i') }}</span>
                                @endif
                            </p>
                            <button type="submit"
                                    class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-5 py-2 rounded-xl transition-colors flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Simpan Roll Call
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit Satu Siswa --}}
    <div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-sm">
            <div class="flex items-center justify-between p-5 border-b border-white/5">
                <h3 class="font-semibold text-white">Edit Status Absensi</h3>
                <button id="btn-close-modal" class="text-gray-500 hover:text-white transition-colors">
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
                        <button type="button" class="status-btn py-2 rounded-xl border border-white/10 text-gray-400 text-xs font-semibold transition-all" data-value="hadir"     data-color="emerald">Hadir</button>
                        <button type="button" class="status-btn py-2 rounded-xl border border-white/10 text-gray-400 text-xs font-semibold transition-all" data-value="terlambat" data-color="amber">Terlambat</button>
                        <button type="button" class="status-btn py-2 rounded-xl border border-white/10 text-gray-400 text-xs font-semibold transition-all" data-value="izin"      data-color="blue">Izin</button>
                        <button type="button" class="status-btn py-2 rounded-xl border border-white/10 text-gray-400 text-xs font-semibold transition-all" data-value="sakit"     data-color="purple">Sakit</button>
                        <button type="button" class="status-btn py-2 rounded-xl border border-white/10 text-gray-400 text-xs font-semibold transition-all" data-value="alfa"      data-color="red">Alfa</button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Alasan / Keterangan <span class="text-red-400">*</span></label>
                    <textarea id="modal-reason" rows="3"
                              class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 resize-none transition-colors"
                              placeholder="Wajib diisi. Dicatat dalam audit log..."></textarea>
                </div>
            </div>
            <div class="p-5 border-t border-white/5 flex gap-3">
                <button id="btn-cancel-modal" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2.5 rounded-xl border border-white/10 transition-colors">Batal</button>
                <button id="btn-submit-edit"  class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Simpan</button>
            </div>
        </div>
    </div>

    {{-- Modal Edit Massal --}}
    <div id="modal-bulk" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-lg">
            <div class="flex items-center justify-between p-5 border-b border-white/5">
                <div>
                    <h3 class="font-semibold text-white">Edit Massal Absensi</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Ubah status banyak siswa sekaligus</p>
                </div>
                <button id="btn-close-bulk" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-gray-400">Pilih Siswa</p>
                        <div class="flex gap-3">
                            <button type="button" id="btn-bulk-check-all"   class="text-xs text-gray-500 hover:text-white transition-colors">Pilih Semua</button>
                            <button type="button" id="btn-bulk-uncheck-all" class="text-xs text-gray-500 hover:text-white transition-colors">Hapus Semua</button>
                        </div>
                    </div>
                    <div class="bg-gray-800 rounded-xl divide-y divide-white/5 max-h-48 overflow-y-auto">
                        @foreach($session->classroom->students->sortBy('name') as $student)
                            @php $att = $recap['attendances']->firstWhere('student_id', $student->id); $st = $att?->status ?? null; @endphp
                            <label class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-white/5 transition-colors">
                                <input type="checkbox" class="bulk-student-cb w-4 h-4 rounded border-gray-600 text-amber-500 focus:ring-amber-500 focus:ring-offset-0"
                                       value="{{ $student->id }}">
                                <span class="flex-1 text-sm text-white">{{ $student->name }}</span>
                                <span class="text-xs text-gray-500">{{ $st ? ucfirst($st) : 'Belum' }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-600 mt-1.5"><span id="bulk-count">0</span> siswa dipilih</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-2">Ubah Status Menjadi</p>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" class="bulk-status-btn py-2 rounded-xl border border-white/10 text-gray-400 text-xs font-semibold transition-all" data-value="hadir"     data-color="emerald">Hadir</button>
                        <button type="button" class="bulk-status-btn py-2 rounded-xl border border-white/10 text-gray-400 text-xs font-semibold transition-all" data-value="terlambat" data-color="amber">Terlambat</button>
                        <button type="button" class="bulk-status-btn py-2 rounded-xl border border-white/10 text-gray-400 text-xs font-semibold transition-all" data-value="izin"      data-color="blue">Izin</button>
                        <button type="button" class="bulk-status-btn py-2 rounded-xl border border-white/10 text-gray-400 text-xs font-semibold transition-all" data-value="sakit"     data-color="purple">Sakit</button>
                        <button type="button" class="bulk-status-btn py-2 rounded-xl border border-white/10 text-gray-400 text-xs font-semibold transition-all" data-value="alfa"      data-color="red">Alfa</button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Alasan / Keterangan <span class="text-red-400">*</span></label>
                    <textarea id="bulk-reason" rows="2"
                              class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 resize-none transition-colors"
                              placeholder="cth: Tidak hadir saat roll call..."></textarea>
                </div>
            </div>
            <div class="p-5 border-t border-white/5 flex gap-3">
                <button id="btn-cancel-bulk" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2.5 rounded-xl border border-white/10 transition-colors">Batal</button>
                <button id="btn-submit-bulk" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Simpan Semua</button>
            </div>
        </div>
    </div>

    {{-- JavaScript langsung di dalam slot --}}
    <script>
    (function() {
        'use strict';

        var SESSION_ID       = {{ $session->id }};
        var IS_ACTIVE        = {{ $session->isActive() ? 'true' : 'false' }};
        var CSRF             = document.querySelector('meta[name="csrf-token"]').content;
        var activeSingleId   = null;
        var activeSingleSt   = null;
        var activeBulkSt     = null;

        // ── Helpers ───────────────────────────────────────────────────────
        function resetBtns(sel) {
            document.querySelectorAll(sel).forEach(function(b) {
                b.classList.remove('border-emerald-500','text-emerald-400','border-amber-500','text-amber-400',
                    'border-blue-500','text-blue-400','border-purple-500','text-purple-400','border-red-500','text-red-400');
                b.classList.add('border-white/10','text-gray-400');
            });
        }
        function highlightBtn(btn, sel) {
            resetBtns(sel);
            var c = btn.dataset.color;
            btn.classList.remove('border-white/10','text-gray-400');
            btn.classList.add('border-'+c+'-500','text-'+c+'-400');
        }

        // ══ EDIT SATU SISWA ═══════════════════════════════════════════════
        var modalEdit = document.getElementById('modal-edit');

        document.querySelectorAll('.btn-edit').forEach(function(btn) {
            btn.addEventListener('click', function() {
                activeSingleId = this.dataset.studentId;
                activeSingleSt = null;
                document.getElementById('modal-student-name').textContent = this.dataset.studentName;
                document.getElementById('modal-reason').value = '';
                resetBtns('.status-btn');
                if (this.dataset.currentStatus) {
                    var b = document.querySelector('.status-btn[data-value="'+this.dataset.currentStatus+'"]');
                    if (b) { highlightBtn(b, '.status-btn'); activeSingleSt = this.dataset.currentStatus; }
                }
                modalEdit.classList.remove('hidden'); modalEdit.classList.add('flex');
            });
        });

        document.querySelectorAll('.status-btn').forEach(function(btn) {
            btn.addEventListener('click', function() { highlightBtn(this,'.status-btn'); activeSingleSt = this.dataset.value; });
        });

        function closeEdit() { modalEdit.classList.add('hidden'); modalEdit.classList.remove('flex'); activeSingleId=null; activeSingleSt=null; }
        document.getElementById('btn-close-modal').addEventListener('click', closeEdit);
        document.getElementById('btn-cancel-modal').addEventListener('click', closeEdit);
        modalEdit.addEventListener('click', function(e) { if(e.target===modalEdit) closeEdit(); });

        document.getElementById('btn-submit-edit').addEventListener('click', async function() {
            if (!activeSingleId) { alert('Data siswa tidak ditemukan.'); return; }
            if (!activeSingleSt) { alert('Pilih status kehadiran.'); return; }
            var reason = document.getElementById('modal-reason').value.trim();
            if (!reason) { alert('Alasan wajib diisi.'); return; }
            this.disabled=true; this.textContent='Menyimpan...';
            try {
                var res  = await fetch('/guru/absensi/sesi/'+SESSION_ID+'/manual', {
                    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
                    body: JSON.stringify({student_id:activeSingleId, status:activeSingleSt, reason:reason}),
                });
                var data = await res.json();
                if (data.success) { closeEdit(); location.reload(); }
                else { alert(data.message||'Gagal menyimpan.'); }
            } catch(e) { alert('Koneksi gagal.'); }
            this.disabled=false; this.textContent='Simpan';
        });

        // ══ EDIT MASSAL ════════════════════════════════════════════════════
        var modalBulk = document.getElementById('modal-bulk');

        function updateBulkCount() {
            document.getElementById('bulk-count').textContent =
                document.querySelectorAll('.bulk-student-cb:checked').length;
        }
        function closeBulk() { modalBulk.classList.add('hidden'); modalBulk.classList.remove('flex'); activeBulkSt=null; }

        document.getElementById('btn-bulk-edit')?.addEventListener('click', function() {
            activeBulkSt = null;
            document.getElementById('bulk-reason').value = '';
            resetBtns('.bulk-status-btn');
            document.querySelectorAll('.bulk-student-cb').forEach(function(c) { c.checked=false; });
            updateBulkCount();
            modalBulk.classList.remove('hidden'); modalBulk.classList.add('flex');
        });

        document.getElementById('btn-close-bulk').addEventListener('click', closeBulk);
        document.getElementById('btn-cancel-bulk').addEventListener('click', closeBulk);
        modalBulk.addEventListener('click', function(e) { if(e.target===modalBulk) closeBulk(); });

        document.getElementById('btn-bulk-check-all').addEventListener('click', function() {
            document.querySelectorAll('.bulk-student-cb').forEach(function(c) { c.checked=true; }); updateBulkCount();
        });
        document.getElementById('btn-bulk-uncheck-all').addEventListener('click', function() {
            document.querySelectorAll('.bulk-student-cb').forEach(function(c) { c.checked=false; }); updateBulkCount();
        });
        document.querySelectorAll('.bulk-student-cb').forEach(function(cb) { cb.addEventListener('change', updateBulkCount); });
        document.querySelectorAll('.bulk-status-btn').forEach(function(btn) {
            btn.addEventListener('click', function() { highlightBtn(this,'.bulk-status-btn'); activeBulkSt=this.dataset.value; });
        });

        document.getElementById('btn-submit-bulk').addEventListener('click', async function() {
            var selected = Array.from(document.querySelectorAll('.bulk-student-cb:checked')).map(function(c){ return c.value; });
            if (selected.length===0)  { alert('Pilih minimal satu siswa.'); return; }
            if (!activeBulkSt)        { alert('Pilih status kehadiran.'); return; }
            var reason = document.getElementById('bulk-reason').value.trim();
            if (!reason) { alert('Alasan wajib diisi.'); return; }

            this.disabled=true; this.textContent='Menyimpan...';
            var errors = [];
            for (var i=0; i<selected.length; i++) {
                try {
                    var res  = await fetch('/guru/absensi/sesi/'+SESSION_ID+'/manual', {
                        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
                        body: JSON.stringify({student_id:selected[i], status:activeBulkSt, reason:reason}),
                    });
                    var data = await res.json();
                    if (!data.success) errors.push(selected[i]);
                } catch(e) { errors.push(selected[i]); }
            }
            this.disabled=false; this.textContent='Simpan Semua';
            if (errors.length>0) alert(errors.length+' siswa gagal disimpan.');
            closeBulk(); location.reload();
        });

        // ══ ROLL CALL ══════════════════════════════════════════════════════
        document.getElementById('btn-check-all')?.addEventListener('click', function() {
            document.querySelectorAll('.roll-call-cb').forEach(function(c){ c.checked=true; });
        });
        document.getElementById('btn-uncheck-all')?.addEventListener('click', function() {
            document.querySelectorAll('.roll-call-cb').forEach(function(c){ c.checked=false; });
        });
        var form = document.getElementById('roll-call-form');
        if (form) {
            form.addEventListener('submit', function() {
                this.querySelectorAll('input[name="absent_ids[]"]').forEach(function(el){ el.remove(); });
                this.querySelectorAll('.roll-call-cb').forEach(function(cb) {
                    if (!cb.checked) {
                        var inp=document.createElement('input'); inp.type='hidden'; inp.name='absent_ids[]'; inp.value=cb.value;
                        form.appendChild(inp);
                    }
                });
            });
        }

        // ══ POLLING REKAP ══════════════════════════════════════════════════
        if (IS_ACTIVE) {
            setInterval(async function() {
                try {
                    var res  = await fetch('/guru/absensi/sesi/'+SESSION_ID+'/rekap');
                    var data = await res.json();
                    if (!data.success) return;
                    var r=data.recap, g=function(id){return document.getElementById(id);};
                    if(g('stat-hadir'))     g('stat-hadir').textContent    = r.hadir+r.terlambat;
                    if(g('stat-terlambat')) g('stat-terlambat').textContent = r.terlambat;
                    if(g('stat-belum'))     g('stat-belum').textContent     = r.belum;
                    if(g('stat-alfa'))      g('stat-alfa').textContent      = r.alfa;
                    if(g('rate-text'))      g('rate-text').textContent      = r.rate+'%';
                    if(g('progress-bar'))   g('progress-bar').style.width   = r.rate+'%';
                } catch(e) {}
            }, 5000);
        }

    })();
    </script>

</x-simans-layout>
