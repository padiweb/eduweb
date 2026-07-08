<x-simans-layout title="Detail Tugas">

    <div class="mb-6">
        <a href="{{ route('siswa.assignments.index') }}"
           class="flex items-center gap-1 text-gray-400 hover:text-white text-sm mb-2 transition-colors w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-white">{{ $assignment->title }}</h1>
        <p class="text-gray-400 text-sm mt-0.5">{{ $assignment->subject->name }} &middot; {{ $assignment->teacher->name }}</p>
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
                @if($assignment->deadline)
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Deadline</p>
                        <p class="text-sm font-semibold {{ $assignment->isPastDeadline() ? 'text-amber-400' : 'text-white' }}">
                            {{ $assignment->deadline->translatedFormat('l, d F Y H:i') }}
                        </p>
                        @if($assignment->isPastDeadline() && ! $assignment->is_closed)
                            <p class="text-xs text-amber-500 mt-0.5">Sudah lewat deadline tapi masih bisa dikumpulkan.</p>
                        @endif
                    </div>
                @endif
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Metode Pengumpulan</p>
                    <p class="text-sm text-white">{{ $assignment->getSubmissionTypeLabel() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Nilai Maksimal</p>
                    <p class="text-sm text-white">{{ $assignment->max_score }}</p>
                </div>
                @if($assignment->description)
                    <div class="border-t border-white/5 pt-3">
                        <p class="text-xs text-gray-500 mb-1">Instruksi</p>
                        <p class="text-sm text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $assignment->description }}</p>
                    </div>
                @endif
                @if($assignment->attachment_path)
                    <div class="border-t border-white/5 pt-3">
                        <p class="text-xs text-gray-500 mb-1.5">Lampiran Soal</p>
                        <a href="{{ asset('storage/'.$assignment->attachment_path) }}" target="_blank"
                           class="flex items-center gap-2 text-sm text-blue-400 hover:text-blue-300 transition-colors bg-blue-500/10 border border-blue-500/20 rounded-xl px-3 py-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/>
                            </svg>
                            Lihat Lampiran Soal
                        </a>
                    </div>
                @endif
            </div>

            {{-- Nilai & komentar guru --}}
            @if($submission && ($submission->score !== null || $submission->feedback))
                <div class="bg-gray-900 border border-{{ $submission->score !== null ? 'emerald' : 'blue' }}-500/20 rounded-xl p-5 space-y-3">

                    {{-- Nilai --}}
                    @if($submission->score !== null)
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Nilaimu</p>
                            <p class="text-4xl font-bold {{ $submission->score >= 80 ? 'text-emerald-400' : ($submission->score >= 60 ? 'text-amber-400' : 'text-red-400') }}">
                                {{ $submission->score }}
                                <span class="text-sm text-gray-500 font-normal">/ {{ $assignment->max_score }}</span>
                            </p>
                        </div>
                    @else
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Nilai</p>
                            <p class="text-sm text-gray-600 italic">Belum dinilai</p>
                        </div>
                    @endif

                    {{-- Komentar guru --}}
                    @if($submission->feedback)
                        <div class="{{ $submission->score !== null ? 'border-t border-white/5 pt-3' : '' }}">
                            <p class="text-xs text-gray-500 mb-1">Komentar Guru</p>
                            <p class="text-sm text-gray-300 leading-relaxed">{{ $submission->feedback }}</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Preview file yang sudah dikumpulkan --}}
            @if($submission && ! $submission->isNotSubmitted() && $submission->hasContent())
                <div class="bg-gray-900 border border-white/5 rounded-xl p-4">
                    <p class="text-xs text-gray-500 mb-2">Jawabanmu</p>

                    @if($submission->file_path)
                        @php $files = array_values(array_filter(explode(',', $submission->file_path))); @endphp
                        <div class="space-y-2">
                            @foreach($files as $i => $fp)
                                @php
                                    $rawName  = basename(trim($fp));
                                    // Hapus prefix timestamp jika ada (format: 1234567890_namafile.ext)
                                    $dispName = preg_replace('/^\d+_/', '', $rawName);
                                    // Potong nama jika terlalu panjang
                                    $ext      = pathinfo($dispName, PATHINFO_EXTENSION);
                                    $base     = pathinfo($dispName, PATHINFO_FILENAME);
                                    $short    = mb_strlen($base) > 20 ? mb_substr($base, 0, 20) . '...' : $base;
                                    $label    = $short . ($ext ? '.'.$ext : '');
                                @endphp
                                <a href="{{ route('siswa.assignments.view-file', ['assignment' => $assignment->id, 'index' => $i]) }}" target="_blank"
                                   class="flex items-center gap-2 text-sm text-blue-400 hover:text-blue-300 transition-colors bg-blue-500/10 border border-blue-500/20 rounded-xl px-3 py-2 min-w-0">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/>
                                    </svg>
                                    <span class="truncate">
                                        {{ count($files) > 1 ? 'File '.($i+1).': ' : '' }}{{ $label }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @elseif($submission->link_url)
                        <a href="{{ $submission->link_url }}" target="_blank"
                           class="text-sm text-blue-400 hover:text-blue-300 transition-colors break-all">
                            {{ $submission->link_url }}
                        </a>
                    @elseif($submission->content)
                        <p class="text-sm text-gray-300 leading-relaxed whitespace-pre-wrap line-clamp-4">{{ $submission->content }}</p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Form kumpul / revisi --}}
        <div class="lg:col-span-2">
            @if($assignment->is_closed)
                <div class="bg-gray-900 border border-white/5 rounded-xl p-8 text-center">
                    <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    <p class="text-gray-400 text-sm font-medium">Tugas sudah ditutup</p>
                    <p class="text-gray-600 text-xs mt-1">Tidak bisa mengumpulkan atau merevisi jawaban.</p>
                </div>
            @elseif($submission && $submission->isNotSubmitted())
                <div class="bg-gray-900 border border-red-500/20 rounded-xl p-8 text-center">
                    <p class="text-red-400 text-sm font-medium">Tidak Mengumpulkan Tugas</p>
                    <p class="text-gray-500 text-xs mt-1">Tugas ini sudah ditutup dan kamu tidak mengumpulkan.</p>
                </div>
            @else
                <div class="bg-gray-900 border {{ $submission ? 'border-emerald-500/20' : 'border-white/5' }} rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-1">
                        {{ $submission ? 'Revisi Jawaban' : 'Kumpulkan Tugas' }}
                    </h2>
                    @if($submission)
                        <p class="text-xs text-gray-500 mb-4">
                            Dikumpulkan {{ $submission->submitted_at->translatedFormat('d M Y H:i') }}
                            @if($submission->isLate()) <span class="text-amber-400">&middot; Terlambat</span> @endif
                            &middot; Kamu bisa merevisi sebelum tugas ditutup.
                        </p>
                    @else
                        <p class="text-xs text-gray-500 mb-4">Kumpulkan jawaban sebelum tugas ditutup.</p>
                    @endif

                    <form method="POST"
                          action="{{ route('siswa.assignments.submit', $assignment->id) }}"
                          enctype="multipart/form-data"
                          id="form-submit"
                          class="space-y-4">
                        @csrf

                        {{-- Teks --}}
                        @if(in_array($assignment->submission_type, ['text', 'any']))
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Jawaban Teks</label>
                                <textarea name="content" rows="5"
                                          class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 resize-none transition-colors"
                                          placeholder="Tulis jawabanmu di sini...">{{ $submission?->content }}</textarea>
                            </div>
                        @endif

                        {{-- Multi-file upload --}}
                        @if(in_array($assignment->submission_type, ['file', 'any']))
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs text-gray-400">Upload File</label>
                                    <button type="button" id="btn-add-file"
                                            class="flex items-center gap-1 text-xs text-emerald-400 hover:text-emerald-300 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                        </svg>
                                        Tambah File
                                    </button>
                                </div>

                                <div id="file-inputs" class="space-y-2">
                                    <div class="file-input-row flex items-center gap-2">
                                        <input type="file" name="files[]"
                                               class="flex-1 bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-gray-700 file:text-gray-300">
                                    </div>
                                </div>

                                @if($submission?->file_path)
                                    @php $existingFiles = array_filter(explode(',', $submission->file_path)); @endphp
                                    <div class="mt-2 space-y-1">
                                        <p class="text-xs text-gray-500">File saat ini (upload baru untuk mengganti):</p>
                                        @foreach($existingFiles as $i => $fp)
                                            <p class="text-xs text-blue-400">{{ $i+1 }}. {{ basename($fp) }}</p>
                                        @endforeach
                                    </div>
                                @endif

                                <p class="text-xs text-gray-600 mt-1.5">Semua jenis file. Maks 50MB per file.</p>
                            </div>
                        @endif

                        {{-- Link --}}
                        @if(in_array($assignment->submission_type, ['link', 'any']))
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Link</label>
                                <input type="url" name="link_url"
                                       value="{{ $submission?->link_url }}"
                                       placeholder="https://..."
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            </div>
                        @endif

                        {{-- Progress upload --}}
                        <div id="upload-progress" class="hidden">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-gray-400">Mengupload file...</span>
                                <span id="progress-pct" class="text-xs text-emerald-400">0%</span>
                            </div>
                            <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                                <div id="progress-bar" class="h-full bg-emerald-500 rounded-full transition-all duration-300" style="width:0%"></div>
                            </div>
                        </div>

                        <button type="submit" id="btn-submit"
                                class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-2xl transition-colors">
                            {{ $submission ? 'Revisi Jawaban' : 'Kumpulkan Tugas' }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    @if(! $assignment->is_closed && ! ($submission && $submission->isNotSubmitted()))
    <script>
    (function() {
        // Tombol tambah file
        document.getElementById('btn-add-file')?.addEventListener('click', function() {
            var container = document.getElementById('file-inputs');
            var row       = document.createElement('div');
            row.className = 'file-input-row flex items-center gap-2';
            row.innerHTML = '<input type="file" name="files[]" class="flex-1 bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-gray-700 file:text-gray-300">'
                + '<button type="button" class="btn-remove-file text-gray-600 hover:text-red-400 transition-colors flex-shrink-0">'
                + '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>'
                + '</button>';
            container.appendChild(row);

            row.querySelector('.btn-remove-file').addEventListener('click', function() {
                container.removeChild(row);
            });
        });

        // Upload dengan progress bar
        var form = document.getElementById('form-submit');
        var btn  = document.getElementById('btn-submit');
        var prog = document.getElementById('upload-progress');
        var bar  = document.getElementById('progress-bar');
        var pct  = document.getElementById('progress-pct');
        var CSRF = document.querySelector('meta[name="csrf-token"]').content;

        form?.addEventListener('submit', function(e) {
            var fileInputs = form.querySelectorAll('input[type="file"]');
            var hasFile    = Array.from(fileInputs).some(function(inp) { return inp.files.length > 0; });
            if (!hasFile) return; // tidak ada file baru, submit biasa

            e.preventDefault();
            btn.disabled    = true;
            btn.textContent = 'Mengupload...';
            prog.classList.remove('hidden');

            var fd  = new FormData(form);
            var xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', function(ev) {
                if (ev.lengthComputable) {
                    var p = Math.round((ev.loaded / ev.total) * 100);
                    bar.style.width = p + '%';
                    pct.textContent = p + '%';
                }
            });

            xhr.addEventListener('load', function() {
                if (xhr.status < 400) {
                    window.location.reload();
                } else {
                    btn.disabled    = false;
                    btn.textContent = '{{ $submission ? "Revisi Jawaban" : "Kumpulkan Tugas" }}';
                    prog.classList.add('hidden');
                    alert('Gagal mengupload. Coba lagi.');
                }
            });

            xhr.addEventListener('error', function() {
                btn.disabled    = false;
                btn.textContent = '{{ $submission ? "Revisi Jawaban" : "Kumpulkan Tugas" }}';
                prog.classList.add('hidden');
                alert('Koneksi gagal.');
            });

            xhr.open('POST', form.action);
            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF);
            xhr.setRequestHeader('Accept', 'text/html,application/xhtml+xml');
            xhr.send(fd);
        });
    })();
    </script>
    @endif

</x-simans-layout>