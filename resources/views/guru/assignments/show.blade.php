<x-simans-layout title="Detail Tugas">

    <div class="flex items-start justify-between mb-6">
        <div>
            <a href="{{ route('guru.assignments.index') }}"
               class="flex items-center gap-1 text-gray-400 hover:text-white text-sm mb-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-white">{{ $assignment->title }}</h1>
            <p class="text-gray-400 text-sm mt-0.5">{{ $assignment->classroom->name }} &middot; {{ $assignment->subject->name }}</p>
        </div>

        @if(! $assignment->is_closed)
            <form method="POST" action="{{ route('guru.assignments.close', $assignment->id) }}"
                  onsubmit="return confirm('Tutup tugas? Siswa yang belum kumpul mendapat poin pelanggaran.')">
                @csrf @method('PATCH')
                <button type="submit"
                        class="flex items-center gap-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 text-sm font-medium px-4 py-2.5 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    Tutup Tugas
                </button>
            </form>
        @else
            <span class="text-xs text-gray-500 bg-gray-800 border border-white/10 px-3 py-2 rounded-xl">
                Ditutup {{ $assignment->closed_at?->translatedFormat('d M Y H:i') }}
            </span>
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

        {{-- Info tugas --}}
        <div class="space-y-4">
            <div class="bg-gray-900 border border-white/5 rounded-xl p-5 space-y-3">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Metode</p>
                    <p class="text-sm font-medium text-white">{{ $assignment->getSubmissionTypeLabel() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Nilai Maksimal</p>
                    <p class="text-sm font-medium text-white">{{ $assignment->max_score }}</p>
                </div>
                @if($assignment->deadline)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Deadline</p>
                        <p class="text-sm font-medium {{ $assignment->isPastDeadline() ? 'text-amber-400' : 'text-white' }}">
                            {{ $assignment->deadline->translatedFormat('l, d F Y H:i') }}
                        </p>
                    </div>
                @endif
                @if($assignment->description)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Instruksi</p>
                        <p class="text-sm text-gray-300 leading-relaxed">{{ $assignment->description }}</p>
                    </div>
                @endif
                @if($assignment->attachment_path)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Lampiran Soal</p>
                        <a href="{{ asset('storage/'.$assignment->attachment_path) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs text-blue-400 hover:text-blue-300 transition-colors">
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
                <div class="bg-gray-900 border border-emerald-500/20 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-emerald-400">{{ $submittedCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Dikumpulkan</p>
                </div>
                <div class="bg-gray-900 border border-red-500/20 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-red-400">{{ $notSubmittedCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Tidak Kumpul</p>
                </div>
                <div class="bg-gray-900 border border-blue-500/20 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-blue-400">{{ $gradedCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Sudah Dinilai</p>
                </div>
                <div class="bg-gray-900 border border-amber-500/20 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-amber-400">{{ $avg ? number_format($avg,1) : '-' }}</p>
                    <p class="text-xs text-gray-500 mt-1">Rata-rata</p>
                </div>
            </div>
        </div>

        {{-- Tabel siswa --}}
        <div class="lg:col-span-2">
            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/5">
                    <h2 class="text-sm font-semibold text-white">Daftar Pengumpulan</h2>
                </div>
                <div class="divide-y divide-white/5">
                    @foreach($students as $student)
                        @php $sub = $submissions->get($student->id); @endphp
                        <div class="px-5 py-4 {{ $sub?->isNotSubmitted() ? 'bg-red-500/[0.03]' : '' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold text-gray-300 flex-shrink-0">
                                    {{ substr($student->name, 0, 2) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate">{{ $student->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        @if(! $sub)
                                            Belum mengumpulkan
                                        @elseif($sub->isNotSubmitted())
                                            <span class="text-red-400">Tidak mengumpulkan tugas</span>
                                        @else
                                            Dikumpulkan {{ $sub->submitted_at->translatedFormat('d M Y H:i') }}
                                            @if($sub->isLate()) <span class="text-amber-400">&middot; Terlambat</span> @endif
                                        @endif
                                    </p>
                                </div>

                                {{-- Badge status --}}
                                @if($sub?->isNotSubmitted())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-red-500/10 text-red-400 border-red-500/20 flex-shrink-0">
                                        Tidak Kumpul
                                    </span>
                                @elseif($sub)
                                    {{-- Tombol lihat jawaban --}}
                                    @if($sub->file_path)
                                        @php $subFiles = array_filter(explode(',', $sub->file_path)); @endphp
                                        @foreach($subFiles as $fi => $fp)
                                            <a href="{{ route('guru.assignments.view-file', [$assignment->id, $sub->id, 'index' => $fi]) }}" target="_blank"
                                               class="text-xs text-blue-400 hover:text-blue-300 px-2 py-1 rounded-lg bg-blue-500/10 border border-blue-500/20 flex-shrink-0">
                                                File {{ count($subFiles) > 1 ? $fi+1 : '' }}
                                            </a>
                                        @endforeach
                                    @elseif($sub->link_url)
                                        <a href="{{ $sub->link_url }}" target="_blank"
                                           class="text-xs text-blue-400 hover:text-blue-300 px-2 py-1 rounded-lg bg-blue-500/10 border border-blue-500/20 flex-shrink-0">
                                            Link
                                        </a>
                                    @elseif($sub->content)
                                        <button onclick="showContent({{ json_encode(substr($sub->content, 0, 1000)) }})"
                                                class="text-xs text-blue-400 hover:text-blue-300 px-2 py-1 rounded-lg bg-blue-500/10 border border-blue-500/20 flex-shrink-0">
                                            Teks
                                        </button>
                                    @endif
                                @endif

                                {{-- Input nilai — selalu tampil jika ada submission (termasuk not_submitted) --}}
                                @if($sub)
                                    <input type="number"
                                           class="score-input w-16 bg-gray-800 border border-white/10 text-white rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:border-emerald-500 transition-colors flex-shrink-0"
                                           placeholder="Nilai"
                                           min="0" max="{{ $assignment->max_score }}"
                                           value="{{ $sub->score ?? '' }}"
                                           data-student-id="{{ $student->id }}"
                                           data-assignment-id="{{ $assignment->id }}">
                                    <button class="btn-save-score text-xs text-emerald-400 hover:text-emerald-300 py-1.5 px-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex-shrink-0"
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
                                               class="comment-input flex-1 bg-gray-800 border border-white/10 text-white rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-blue-500 transition-colors"
                                               placeholder="{{ $sub->isNotSubmitted() ? 'Komentar untuk siswa yang tidak kumpul (opsional)...' : 'Komentar / catatan revisi (opsional)...' }}"
                                               value="{{ $sub->feedback ?? '' }}"
                                               data-student-id="{{ $student->id }}"
                                               data-assignment-id="{{ $assignment->id }}">
                                        <button class="btn-save-comment text-xs text-blue-400 hover:text-blue-300 py-1.5 px-2 rounded-lg bg-blue-500/10 border border-blue-500/20 flex-shrink-0"
                                                data-student-id="{{ $student->id }}">
                                            Kirim
                                        </button>
                                    </div>
                                    @if($sub->feedback)
                                        <p class="text-xs text-gray-600 mt-1">{{ $sub->feedback }}</p>
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
    <div id="modal-content" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-lg">
            <div class="flex items-center justify-between p-5 border-b border-white/5">
                <h3 class="font-semibold text-white">Jawaban Siswa</h3>
                <button id="close-content" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 max-h-96 overflow-y-auto">
                <p id="content-text" class="text-sm text-gray-300 whitespace-pre-wrap leading-relaxed"></p>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var CSRF = document.querySelector('meta[name="csrf-token"]').content;

        window.showContent = function(text) {
            document.getElementById('content-text').textContent = text;
            var m = document.getElementById('modal-content');
            m.classList.remove('hidden'); m.classList.add('flex');
        };
        document.getElementById('close-content')?.addEventListener('click', function() {
            var m = document.getElementById('modal-content');
            m.classList.add('hidden'); m.classList.remove('flex');
        });

        async function saveGrade(studentId, score, feedback) {
            var input = document.querySelector('.score-input[data-student-id="'+studentId+'"]');
            var asgId = input?.dataset.assignmentId;
            try {
                var res  = await fetch('/guru/tugas/'+asgId+'/nilai', {
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
                    body: JSON.stringify({student_id:studentId, score:parseInt(score), feedback:feedback}),
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

                if (!score) { alert('Masukkan nilai terlebih dahulu.'); return; }
                this.disabled=true; this.textContent='...';

                var data = await saveGrade(sid, score, feedback);
                if (data.success) {
                    this.textContent='Tersimpan';
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
                var score    = input?.value;
                var feedback = cmtInput?.value ?? '';

                if (!score) { alert('Isi nilai dulu sebelum mengirim komentar.'); return; }
                this.disabled=true; this.textContent='...';

                var data = await saveGrade(sid, score, feedback);
                if (data.success) {
                    this.textContent='Terkirim';
                    setTimeout(() => { this.textContent='Kirim'; this.disabled=false; }, 2000);
                } else {
                    alert(data.message||'Gagal.'); this.disabled=false; this.textContent='Kirim';
                }
            });
        });
    })();
    </script>

</x-simans-layout>