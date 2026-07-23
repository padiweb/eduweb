<x-simans-layout title="Detail Tugas">

{{-- Header --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap">
    <div>
        <a href="{{ route('guru.assignments.index') }}"
           style="display:inline-flex;align-items:center;gap:4px;font-size:13px;color:#64748b;text-decoration:none;margin-bottom:8px">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Kembali
        </a>
        <h1 style="font-size:20px;font-weight:800;color:#0f172a;margin:0 0 4px">{{ $assignment->title }}</h1>
        <p style="font-size:13px;color:#64748b;margin:0">{{ $assignment->classroom->name }} · {{ $assignment->subject->name }}</p>
    </div>

    @if(! $assignment->is_closed)
        <form method="POST" action="{{ route('guru.assignments.close', $assignment->id) }}"
              onsubmit="return confirm('Tutup tugas? Siswa yang belum kumpul mendapat poin pelanggaran.')">
            @csrf @method('PATCH')
            <button type="submit"
                    style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;background:#fff1f2;border:1.5px solid #fca5a5;color:#dc2626;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
                Tutup Tugas
            </button>
        </form>
    @else
        <span style="font-size:12px;color:#94a3b8;background:#f8fafc;border:1px solid #e2e8f0;padding:8px 14px;border-radius:10px;white-space:nowrap">
            Ditutup {{ $assignment->closed_at?->translatedFormat('d M Y H:i') }}
        </span>
    @endif
</div>

@if(session('success'))
    <div style="display:flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:12px;font-size:13px;font-weight:500;margin-bottom:16px">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="display:flex;align-items:center;gap:8px;background:#fff1f2;border:1px solid #fecaca;color:#dc2626;padding:12px 16px;border-radius:12px;font-size:13px;font-weight:500;margin-bottom:16px">
        {{ session('error') }}
    </div>
@endif

{{-- Layout: Info + Daftar --}}
<div id="tugas-grid" style="display:grid;grid-template-columns:1fr 2fr;gap:16px">

    {{-- Kolom kiri: Info tugas + stat cards --}}
    <div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;margin-bottom:12px">
            <h2 style="font-size:13px;font-weight:700;color:#0f172a;margin:0 0 14px">Info Tugas</h2>
            <div style="display:flex;flex-direction:column;gap:12px">
                <div>
                    <p style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin:0 0 3px">Metode</p>
                    <p style="font-size:13px;font-weight:600;color:#334155;margin:0">{{ $assignment->getSubmissionTypeLabel() }}</p>
                </div>
                <div>
                    <p style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin:0 0 3px">Nilai Maks</p>
                    <p style="font-size:13px;font-weight:600;color:#334155;margin:0">{{ $assignment->max_score }}</p>
                </div>
                @if($assignment->deadline)
                <div>
                    <p style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin:0 0 3px">Deadline</p>
                    <p style="font-size:13px;font-weight:600;color:{{ $assignment->isPastDeadline() ? '#d97706' : '#334155' }};margin:0">
                        {{ $assignment->deadline->translatedFormat('d M Y, H:i') }}
                    </p>
                </div>
                @endif
                @if($assignment->description)
                <div>
                    <p style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin:0 0 3px">Instruksi</p>
                    <p style="font-size:12.5px;color:#475569;line-height:1.5;margin:0">{{ $assignment->description }}</p>
                </div>
                @endif
                @if($assignment->attachment_path)
                <div>
                    <p style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin:0 0 3px">Lampiran Soal</p>
                    <a href="{{ asset('storage/'.$assignment->attachment_path) }}" target="_blank"
                       style="display:inline-flex;align-items:center;gap:5px;font-size:12px;color:#2563eb;font-weight:600;text-decoration:none">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32"/></svg>
                        Lihat Lampiran
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Stat cards --}}
        @php
            $notSubmittedCount = $submissions->where('status','not_submitted')->count();
            $submittedCount    = $submissions->whereNotIn('status',['not_submitted'])->count();
            $gradedCount       = $submissions->where('status','graded')->count();
            $avg               = $submissions->whereNotNull('score')->avg('score');
        @endphp
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div style="background:#fff;border:1.5px solid #bfdbfe;border-radius:12px;padding:14px;text-align:center">
                <p style="font-size:24px;font-weight:800;color:#2563eb;margin:0">{{ $submittedCount }}</p>
                <p style="font-size:11px;color:#64748b;margin:4px 0 0">Dikumpulkan</p>
            </div>
            <div style="background:#fff;border:1.5px solid #fecaca;border-radius:12px;padding:14px;text-align:center">
                <p style="font-size:24px;font-weight:800;color:#dc2626;margin:0">{{ $notSubmittedCount }}</p>
                <p style="font-size:11px;color:#64748b;margin:4px 0 0">Tidak Kumpul</p>
            </div>
            <div style="background:#fff;border:1.5px solid #bfdbfe;border-radius:12px;padding:14px;text-align:center">
                <p style="font-size:24px;font-weight:800;color:#2563eb;margin:0">{{ $gradedCount }}</p>
                <p style="font-size:11px;color:#64748b;margin:4px 0 0">Sudah Dinilai</p>
            </div>
            <div style="background:#fff;border:1.5px solid #fde68a;border-radius:12px;padding:14px;text-align:center">
                <p style="font-size:24px;font-weight:800;color:#d97706;margin:0">{{ $avg ? number_format($avg,1) : '-' }}</p>
                <p style="font-size:11px;color:#64748b;margin:4px 0 0">Rata-rata</p>
            </div>
        </div>
    </div>

    {{-- Kolom kanan: Daftar siswa --}}
    <div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden">
            <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9">
                <h2 style="font-size:13px;font-weight:700;color:#0f172a;margin:0">Daftar Pengumpulan</h2>
            </div>

            @foreach($students as $student)
                @php $sub = $submissions->get($student->id); @endphp
                <div style="padding:14px 20px;border-bottom:1px solid #f8fafc;{{ $sub?->isNotSubmitted() ? 'background:#fffbeb' : '' }}">

                    {{-- Baris atas: avatar + nama + waktu --}}
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:{{ $sub ? '10px' : '0' }}">
                        <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#e0e7ff,#c7d2fe);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#4338ca;flex-shrink:0">
                            {{ strtoupper(substr($student->name, 0, 2)) }}
                        </div>
                        <div style="flex:1;min-width:0">
                            <p style="font-size:13.5px;font-weight:600;color:#0f172a;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $student->name }}</p>
                            <p style="font-size:11px;color:#94a3b8;margin:2px 0 0">
                                @if(!$sub)
                                    Belum mengumpulkan
                                @elseif($sub->isNotSubmitted())
                                    <span style="color:#dc2626;font-weight:600">Tidak mengumpulkan</span>
                                @elseif($sub->submitted_at)
                                    @php $sat = $sub->submitted_at instanceof \Carbon\Carbon ? $sub->submitted_at : \Carbon\Carbon::parse($sub->submitted_at); @endphp
                                    Dikumpulkan {{ $sat->translatedFormat('d M Y H:i') }}
                                    @if($sub->isLate()) · <span style="color:#d97706">Terlambat</span> @endif
                                @else
                                    Dikumpulkan
                                @endif
                            </p>
                        </div>

                        {{-- Badge tidak kumpul --}}
                        @if($sub?->isNotSubmitted())
                            <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:#fff1f2;color:#dc2626;border:1px solid #fecaca;flex-shrink:0;white-space:nowrap">
                                Tidak Kumpul
                            </span>
                        @endif
                    </div>

                    @if($sub)
                        {{-- Baris jawaban: tombol lihat + nilai + simpan (wrap di mobile) --}}
                        <div style="margin-left:44px;display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin-bottom:8px">

                            {{-- Semua jenis jawaban --}}
                            @if($sub->file_path && !$sub->isNotSubmitted())
                                @php $subFiles = array_filter(explode(',', $sub->file_path)); @endphp
                                @foreach($subFiles as $fi => $fp)
                                    <a href="{{ route('guru.assignments.view-file', [$assignment->id, $sub->id, 'index' => $fi]) }}" target="_blank"
                                       style="display:inline-flex;align-items:center;gap:4px;font-size:11.5px;font-weight:600;padding:5px 10px;background:#eff6ff;border:1px solid #bfdbfe;color:#2563eb;text-decoration:none;border-radius:8px;white-space:nowrap">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        File{{ count($subFiles) > 1 ? ' '.($fi+1) : '' }}
                                    </a>
                                @endforeach
                            @endif
                            @if($sub->link_url && !$sub->isNotSubmitted())
                                <a href="{{ $sub->link_url }}" target="_blank"
                                   style="display:inline-flex;align-items:center;gap:4px;font-size:11.5px;font-weight:600;padding:5px 10px;background:#f5f3ff;border:1px solid #ddd6fe;color:#7c3aed;text-decoration:none;border-radius:8px;white-space:nowrap">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/></svg>
                                    Link
                                </a>
                            @endif
                            @if($sub->content && !$sub->isNotSubmitted())
                                <button onclick="showContent({{ json_encode($sub->student->name) }}, {{ json_encode($sub->content) }})"
                                        style="display:inline-flex;align-items:center;gap:4px;font-size:11.5px;font-weight:600;padding:5px 10px;background:#ecfdf5;border:1px solid #bbf7d0;color:#059669;border-radius:8px;cursor:pointer;white-space:nowrap">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                    Teks
                                </button>
                            @endif

                            {{-- Nilai --}}
                            <div style="display:flex;align-items:center;gap:6px;margin-left:auto">
                                <input type="number"
                                       class="score-input"
                                       data-student-id="{{ $student->id }}"
                                       data-assignment-id="{{ $assignment->id }}"
                                       placeholder="Nilai"
                                       min="0" max="{{ $assignment->max_score }}"
                                       value="{{ $sub->score ?? '' }}"
                                       style="width:64px;border:1.5px solid #e2e8f0;border-radius:8px;padding:5px 8px;font-size:13px;text-align:center;color:#334155;outline:none">
                                <button class="btn-save-score"
                                        data-student-id="{{ $student->id }}"
                                        style="font-size:11.5px;font-weight:600;padding:5px 12px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;border:none;border-radius:8px;cursor:pointer;white-space:nowrap">
                                    Simpan
                                </button>
                            </div>
                        </div>

                        {{-- Komentar --}}
                        <div style="margin-left:44px">
                            <div style="display:flex;align-items:center;gap:6px">
                                <input type="text"
                                       class="comment-input"
                                       data-student-id="{{ $student->id }}"
                                       data-assignment-id="{{ $assignment->id }}"
                                       placeholder="Komentar / catatan revisi (opsional)..."
                                       value="{{ $sub->feedback ?? '' }}"
                                       style="flex:1;min-width:0;border:1.5px solid #e2e8f0;border-radius:8px;padding:6px 10px;font-size:12px;color:#334155;outline:none">
                                <button class="btn-save-comment"
                                        data-student-id="{{ $student->id }}"
                                        style="font-size:11.5px;font-weight:600;padding:6px 12px;background:#f8fafc;border:1.5px solid #e2e8f0;color:#475569;border-radius:8px;cursor:pointer;white-space:nowrap;flex-shrink:0">
                                    Kirim
                                </button>
                            </div>
                            @if($sub->feedback)
                                <p style="font-size:11.5px;color:#64748b;margin:6px 0 0;font-style:italic">"{{ $sub->feedback }}"</p>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
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

<style>
/* Mobile: stack layout vertikal */
@media (max-width: 767px) {
    #tugas-grid {
        grid-template-columns: 1fr !important;
    }
    /* Stat cards 2x2 tetap */
    #tugas-grid > div:first-child > div:last-child {
        grid-template-columns: 1fr 1fr !important;
    }
    /* Baris jawaban: nilai ke bawah */
    #tugas-grid .wrap-answers {
        flex-direction: column;
        align-items: flex-start;
    }
    #tugas-grid .wrap-answers .nilai-group {
        width: 100%;
        justify-content: flex-start;
        margin-left: 0 !important;
    }
}
</style>

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
            var res = await fetch('/guru/tugas/'+asgId+'/nilai', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    student_id: studentId,
                    score: score !== '' && score !== null ? parseInt(score) : null,
                    feedback: feedback
                }),
            });
            return await res.json();
        } catch(e) { return { success: false }; }
    }

    document.querySelectorAll('.btn-save-score').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            var sid      = this.dataset.studentId;
            var input    = document.querySelector('.score-input[data-student-id="'+sid+'"]');
            var cmtInput = document.querySelector('.comment-input[data-student-id="'+sid+'"]');
            var score    = input?.value;
            var feedback = cmtInput?.value ?? '';
            if (!score && score !== '0') { alert('Masukkan nilai terlebih dahulu.'); return; }
            this.disabled = true; this.textContent = '...';
            var data = await saveGrade(sid, score, feedback);
            if (data.success) {
                this.textContent = '✓ Tersimpan';
                this.style.background = 'linear-gradient(135deg,#10b981,#059669)';
                setTimeout(() => {
                    this.textContent = 'Simpan';
                    this.style.background = 'linear-gradient(135deg,#3b82f6,#2563eb)';
                    this.disabled = false;
                }, 2000);
            } else {
                alert(data.message || 'Gagal menyimpan.');
                this.disabled = false; this.textContent = 'Simpan';
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
            this.disabled = true; this.textContent = '...';
            var data = await saveGrade(sid, score || null, feedback);
            if (data.success) {
                this.textContent = '✓ Terkirim';
                setTimeout(() => { this.textContent = 'Kirim'; this.disabled = false; }, 2000);
            } else {
                alert(data.message || 'Gagal.'); this.disabled = false; this.textContent = 'Kirim';
            }
        });
    });
})();
</script>

</x-simans-layout>
