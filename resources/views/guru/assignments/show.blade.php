<x-simans-layout title="Detail Tugas">

    <div class="flex items-start justify-between mb-6">
        <div>
            <a href="{{ route('guru.assignments.index') }}"
               class="flex items-center gap-1 text-gray-500 hover:text-blue-600 text-sm mb-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $assignment->title }}</h1>
            <p class="text-gray-500 text-sm mt-0.5">{{ $assignment->classroom->name }} &middot; {{ $assignment->subject->name }}</p>
        </div>

        @if(! $assignment->is_closed)
            <form method="POST" action="{{ route('guru.assignments.close', $assignment->id) }}"
                  onsubmit="return confirm('Tutup tugas? Siswa yang belum kumpul mendapat poin pelanggaran.')">
                @csrf @method('PATCH')
                <button type="submit"
                        class="flex items-center gap-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 text-sm font-medium px-4 py-2.5 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    Tutup Tugas
                </button>
            </form>
        @else
            <span class="text-xs text-gray-500 bg-white border border-gray-200 px-3 py-2 rounded-xl">
                Ditutup {{ $assignment->closed_at?->translatedFormat('d M Y H:i') }}
            </span>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Info tugas --}}
        <div class="space-y-4">
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-3">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Metode</p>
                    <p class="text-sm font-medium text-gray-900">{{ $assignment->getSubmissionTypeLabel() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Nilai Maksimal</p>
                    <p class="text-sm font-medium text-gray-900">{{ $assignment->max_score }}</p>
                </div>
                @if($assignment->deadline)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Deadline</p>
                        <p class="text-sm font-medium {{ $assignment->isPastDeadline() ? 'text-amber-600' : 'text-gray-900' }}">
                            {{ $assignment->deadline->translatedFormat('l, d F Y H:i') }}
                        </p>
                    </div>
                @endif
                @if($assignment->description)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Instruksi</p>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $assignment->description }}</p>
                    </div>
                @endif
                @if($assignment->attachment_path)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Lampiran Soal</p>
                        <a href="{{ asset('storage/'.$assignment->attachment_path) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/>
                            </svg>
                            Lihat Lampiran
                        </a>
                    </div>
                @endif
            </div>

            {{-- Stat cards --}}
            @php
                $notSubmittedCount = $submissions->where('status','not_submitted')->count();
                $submittedCount    = $submissions->whereNotIn('status',['not_submitted'])->count();
                $gradedCount       = $submissions->where('status','graded')->count();
                $avg               = $submissions->whereNotNull('score')->avg('score');
            @endphp
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white border border-blue-200 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ $submittedCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Dikumpulkan</p>
                </div>
                <div class="bg-white border border-red-200 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-red-600">{{ $notSubmittedCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Tidak Kumpul</p>
                </div>
                <div class="bg-white border border-blue-200 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ $gradedCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Sudah Dinilai</p>
                </div>
                <div class="bg-white border border-amber-200 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-amber-600">{{ $avg ? number_format($avg,1) : '-' }}</p>
                    <p class="text-xs text-gray-500 mt-1">Rata-rata</p>
                </div>
            </div>
        </div>

        {{-- Tabel siswa --}}
        <div class="lg:col-span-2">
            <div class="tbl-card">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-900">Daftar Pengumpulan</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($students as $student)
                        @php $sub = $submissions->get($student->id); @endphp
                        <div class="px-5 py-4 {{ $sub?->isNotSubmitted() ? 'bg-red-500/[0.03]' : '' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-xs font-bold text-gray-600 flex-shrink-0">
                                    {{ substr($student->name, 0, 2) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $student->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        @if(! $sub)
                                            Belum mengumpulkan
                                        @elseif($sub->isNotSubmitted())
                                            <span class="text-red-600">Tidak mengumpulkan tugas</span>
                                        @elseif($sub->submitted_at && $sub->submitted_at instanceof \Carbon\Carbon)
                                            Dikumpulkan {{ $sub->submitted_at->translatedFormat('d M Y H:i') }}
                                            @if($sub->isLate()) <span class="text-amber-600">&middot; Terlambat</span> @endif
                                        @elseif($sub->submitted_at)
                                            Dikumpulkan {{ \Carbon\Carbon::parse($sub->submitted_at)->translatedFormat('d M Y H:i') }}
                                        @else
                                            <span class="text-gray-500">Dikumpulkan</span>
                                        @endif
                                    </p>
                                </div>

                                {{-- Badge status --}}
                                @if($sub?->isNotSubmitted())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-red-50 text-red-700 border-red-200 flex-shrink-0">
                                        Tidak Kumpul
                                    </span>
                                @elseif($sub)
                                    {{-- Tombol lihat jawaban (SEMUA jenis ditampilkan) --}}
                                    @if($sub->file_path)
                                        @php $subFiles = array_filter(explode(',', $sub->file_path)); @endphp
                                        @foreach($subFiles as $fi => $fp)
                                            <a href="{{ route('guru.assignments.view-file', [$assignment->id, $sub->id, 'index' => $fi]) }}" target="_blank"
                                               style="font-size:11px;font-weight:600;padding:4px 10px;border-radius:8px;background:#eff6ff;border:1px solid #bfdbfe;color:#2563eb;text-decoration:none;white-space:nowrap;flex-shrink:0;display:inline-flex;align-items:center;gap:3px">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                File{{ count($subFiles) > 1 ? ' '.($fi+1) : '' }}
                                            </a>
                                        @endforeach
                                    @endif
                                    @if($sub->link_url)
                                        <a href="{{ $sub->link_url }}" target="_blank"
                                           style="font-size:11px;font-weight:600;padding:4px 10px;border-radius:8px;background:#f5f3ff;border:1px solid #ddd6fe;color:#7c3aed;text-decoration:none;white-space:nowrap;flex-shrink:0;display:inline-flex;align-items:center;gap:3px">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/><path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 015.656 0l4-4a4 4 0 01-5.656-5.656l-1.1 1.1"/></svg>
                                            Link
                                        </a>
                                    @endif
                                    @if($sub->content)
                                        <button onclick="showContent({{ json_encode($sub->student->name) }}, {{ json_encode($sub->content) }})"
                                               style="font-size:11px;font-weight:600;padding:4px 10px;border-radius:8px;background:#ecfdf5;border:1px solid #bbf7d0;color:#059669;cursor:pointer;white-space:nowrap;flex-shrink:0;display:inline-flex;align-items:center;gap:3px">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                            Teks
                                        </button>
                                    @endif
                                @endif

                                {{-- Input nilai — selalu tampil jika ada submission (termasuk not_submitted) --}}
                                @if($sub)
                                    <input type="number"
                                           class="score-input w-16 bg-white border border-gray-200 text-gray-700 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:border-blue-500 transition-colors flex-shrink-0"
                                           placeholder="Nilai"
                                           min="0" max="{{ $assignment->max_score }}"
                                           value="{{ $sub->score ?? '' }}"
                                           data-student-id="{{ $student->id }}"
                                           data-assignment-id="{{ $assignment->id }}">
                                    <button class="btn-save-score text-xs text-blue-600 hover:text-blue-700 py-1.5 px-2 rounded-lg bg-blue-50 border border-blue-200 flex-shrink-0"
                                            data-student-id="{{ $student->id }}">
                                        Simpan
                                    </button>
                                @endif
                            </div>

                            {{-- Komentar guru --}}
                            @if($sub)
                                <div class="mt-2 ml-11">
                                    <div class="flex items-center gap-2">
                                        <input type="text"
                                               class="comment-input flex-1 bg-white border border-gray-200 text-gray-700 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-blue-500 transition-colors"
                                               placeholder="{{ $sub->isNotSubmitted() ? 'Komentar untuk siswa yang tidak kumpul (opsional)...' : 'Komentar / catatan revisi (opsional)...' }}"
                                               value="{{ $sub->feedback ?? '' }}"
                                               data-student-id="{{ $student->id }}"
                                               data-assignment-id="{{ $assignment->id }}">
                                        <button class="btn-save-comment text-xs text-blue-600 hover:text-blue-600 py-1.5 px-2 rounded-lg bg-blue-50 border border-blue-200 flex-shrink-0"
                                                data-student-id="{{ $student->id }}">
                                            Kirim
                                        </button>
                                    </div>
                                    @if($sub->feedback)
                                        <p class="text-xs text-gray-500 mt-1">{{ $sub->feedback }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Modal lihat konten teks --}}
    <div id="modal-content" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.6);padding:16px">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;width:100%;max-width:560px;box-shadow:0 20px 60px rgba(15,23,42,0.25);max-height:85vh;display:flex;flex-direction:column">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f1f5f9;flex-shrink:0">
                <div>
                    <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0">Jawaban Siswa</h3>
                    <p id="content-student" style="font-size:12px;color:#64748b;margin:2px 0 0"></p>
                </div>
                <button onclick="closeContent()" style="width:30px;height:30px;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;cursor:pointer">
                    <svg width="16" height="16" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div style="padding:20px;overflow-y:auto;flex:1">
                <pre id="content-text" style="font-size:13.5px;color:#334155;white-space:pre-wrap;line-height:1.7;font-family:inherit;margin:0"></pre>
            </div>
            <div style="padding:12px 20px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;flex-shrink:0">
                <button onclick="closeContent()" style="padding:8px 20px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#475569;cursor:pointer">Tutup</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var CSRF = document.querySelector('meta[name="csrf-token"]').content;

        window.showContent = function(studentName, text) {
            document.getElementById('content-text').textContent = text || '';
            document.getElementById('content-student').textContent = studentName || '';
            var m = document.getElementById('modal-content');
            m.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };
        window.closeContent = function() {
            document.getElementById('modal-content').style.display = 'none';
            document.body.style.overflow = '';
        };
        document.getElementById('modal-content')?.addEventListener('click', function(e) {
            if (e.target === this) window.closeContent();
        });

        async function saveGrade(studentId, score, feedback) {
            var input = document.querySelector('.score-input[data-student-id="'+studentId+'"]');
            var asgId = input?.dataset.assignmentId;
            try {
                var res  = await fetch('/guru/tugas/'+asgId+'/nilai', {
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
                    body: JSON.stringify({
                        student_id: studentId,
                        score:      score !== '' && score !== null ? parseInt(score) : null,
                        feedback:   feedback
                    }),
                });
                return await res.json();
            } catch(e) { return {success:false}; }
        }

        document.querySelectorAll('.btn-save-score').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                var sid      = this.dataset.studentId;
                var input    = document.querySelector('.score-input[data-student-id="'+sid+'"]');
                var cmtInput = document.querySelector('.comment-input[data-student-id="'+sid+'"]');
                var score    = input?.value;
                var feedback = cmtInput?.value ?? '';

                if (!score && score !== '0') { alert('Masukkan nilai terlebih dahulu.'); return; }
                this.disabled=true; this.textContent='...';

                var data = await saveGrade(sid, score, feedback);
                if (data.success) {
                    this.textContent='Tersimpan ✓';
                    setTimeout(() => { this.textContent='Simpan'; this.disabled=false; }, 2000);
                } else {
                    alert(data.message||'Gagal menyimpan.');
                    this.disabled=false; this.textContent='Simpan';
                }
            });
        });

        document.querySelectorAll('.btn-save-comment').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                var sid      = this.dataset.studentId;
                var input    = document.querySelector('.score-input[data-student-id="'+sid+'"]');
                var cmtInput = document.querySelector('.comment-input[data-student-id="'+sid+'"]');
                var score    = input?.value ?? '';
                var feedback = cmtInput?.value ?? '';

                if (!feedback.trim()) { alert('Tulis komentar terlebih dahulu.'); return; }
                this.disabled=true; this.textContent='...';

                // Kirim dengan score yang ada (atau null jika belum diisi)
                var data = await saveGrade(sid, score || null, feedback);
                if (data.success) {
                    this.textContent='Terkirim ✓';
                    setTimeout(() => { this.textContent='Kirim'; this.disabled=false; }, 2000);
                } else {
                    alert(data.message||'Gagal.'); this.disabled=false; this.textContent='Kirim';
                }
            });
        });
    })();
    </script>

</x-simans-layout>