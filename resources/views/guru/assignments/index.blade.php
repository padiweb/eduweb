<x-simans-layout title="Tugas & Nilai">

    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Tugas & Nilai</h1>
            <p class="text-gray-400 text-sm mt-1">Kelola tugas dan nilai siswa per mata pelajaran</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('guru.assignments.scores') }}"
               class="flex items-center gap-2 text-sm font-medium text-gray-400 hover:text-white bg-gray-800 border border-white/10 px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                Rekap Nilai
            </a>
            <button id="btn-tambah-tugas"
                    class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Buat Tugas
            </button>
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

    @if($assignments->isEmpty())
        <div class="bg-gray-900 border border-white/5 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            <p class="text-gray-500 text-sm">Belum ada tugas. Klik Buat Tugas untuk memulai.</p>
        </div>
    @else
        <div class="space-y-5">
            @foreach($assignments as $groupKey => $groupItems)
                @php
                    $first     = $groupItems->first();
                    $classroom = $first->classroom;
                    $subject   = $first->subject;
                    $total     = $groupItems->count();
                    $aktif     = $groupItems->where('is_closed', false)->count();
                    $tutup     = $groupItems->where('is_closed', true)->count();
                @endphp
                <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                    {{-- Header grup: Kelas + Mapel --}}
                    <div class="px-5 py-3.5 border-b border-white/5 bg-white/[0.02] flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div>
                                <p class="text-sm font-bold text-white">{{ $classroom->name }} — {{ $subject->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $total }} tugas &middot;
                                    @if($aktif > 0) <span class="text-emerald-400">{{ $aktif }} aktif</span> @endif
                                    @if($aktif > 0 && $tutup > 0) &middot; @endif
                                    @if($tutup > 0) <span class="text-gray-500">{{ $tutup }} ditutup</span> @endif
                                </p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-600">Tugas ke-1 s/d {{ $total }}</span>
                    </div>

                    {{-- Daftar tugas dalam grup --}}
                    <div class="divide-y divide-white/5">
                        @foreach($groupItems as $no => $a)
                            @php
                                $statusColor = $a->is_closed ? 'gray' : ($a->isPastDeadline() ? 'amber' : 'emerald');
                                $statusLabel = $a->is_closed ? 'Ditutup' : ($a->isPastDeadline() ? 'Lewat Deadline' : 'Aktif');
                            @endphp
                            <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/[0.02] transition-colors">
                                <span class="text-xs text-gray-600 w-6 text-right flex-shrink-0">{{ $no + 1 }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-white truncate">{{ $a->title }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $a->submissions_count }} kumpul
                                        @if($a->deadline) &middot; Deadline: {{ $a->deadline->translatedFormat('d M Y H:i') }} @endif
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border flex-shrink-0
                                    {{ $statusColor === 'emerald' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' :
                                       ($statusColor === 'amber'  ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' :
                                       'bg-gray-800 text-gray-400 border-white/10') }}">
                                    {{ $statusLabel }}
                                </span>
                                <a href="{{ route('guru.assignments.show', $a->id) }}"
                                   class="text-xs text-emerald-400 hover:text-emerald-300 transition-colors flex-shrink-0">
                                    Detail &rarr;
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal Buat Tugas --}}
    <div id="modal-tugas" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-5 border-b border-white/5 sticky top-0 bg-gray-900 z-10">
                <h3 class="font-semibold text-white">Buat Tugas Baru</h3>
                <button id="btn-close-modal" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('guru.assignments.store') }}" enctype="multipart/form-data" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Kelas <span class="text-red-400">*</span></label>
                    <select name="classroom_id" id="select-classroom" required
                            class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        <option value="">Pilih kelas...</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->students->count() }} siswa)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Mata Pelajaran <span class="text-red-400">*</span></label>
                    <select name="subject_id" id="select-subject" required
                            class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        <option value="">Pilih kelas dulu...</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" data-subject-id="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @if($subjects->isEmpty())
                        <p class="text-xs text-amber-500 mt-1">Belum ada jadwal mengajar. Hubungi admin untuk mengatur jadwal.</p>
                    @endif
                </div>

                {{-- Data jadwal guru (mapel per kelas) untuk JS --}}
                <script>
                var scheduleMap = @json($scheduleMap);
                var allSubjects = @json($subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values());

                document.getElementById('select-classroom').addEventListener('change', function() {
                    var classroomId = parseInt(this.value);
                    var subjectSel  = document.getElementById('select-subject');

                    // Filter mapel yang diampu di kelas ini
                    var validSubjectIds = scheduleMap
                        .filter(function(s) { return s.classroom_id === classroomId; })
                        .map(function(s) { return s.subject_id; });

                    subjectSel.innerHTML = '';

                    if (!classroomId || validSubjectIds.length === 0) {
                        subjectSel.innerHTML = '<option value="">-- Pilih mapel --</option>';
                        return;
                    }

                    var filtered = allSubjects.filter(function(s) {
                        return validSubjectIds.includes(s.id);
                    });

                    filtered.forEach(function(s) {
                        var opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.name;
                        subjectSel.appendChild(opt);
                    });

                    // Auto-select jika hanya 1 mapel
                    if (filtered.length === 1) subjectSel.value = filtered[0].id;
                });
                </script>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Judul Tugas <span class="text-red-400">*</span></label>
                    <input type="text" name="title" required placeholder="cth: Tugas 1 - Persamaan Linear"
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Deskripsi / Instruksi</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 resize-none transition-colors"
                              placeholder="Jelaskan instruksi tugas..."></textarea>
                </div>
                {{-- File lampiran soal dari guru --}}
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Lampiran Soal (opsional)</label>
                    <input type="file" name="attachment_path"
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors file:mr-3 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-gray-700 file:text-gray-300">
                    <p class="text-xs text-gray-600 mt-1">Semua jenis file. Maks 10MB.</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Metode Pengumpulan <span class="text-red-400">*</span></label>
                        <select name="submission_type" required
                                class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <option value="any">Semua (file/teks/link)</option>
                            <option value="file">File saja</option>
                            <option value="text">Teks saja</option>
                            <option value="link">Link saja</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Nilai Maksimal <span class="text-red-400">*</span></label>
                        <input type="number" name="max_score" value="100" min="1" max="100" required
                               class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Deadline (opsional)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <input type="date" name="deadline_date" id="deadline_date"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <p class="text-xs text-gray-600 mt-1">Tanggal (dd/mm/yyyy)</p>
                        </div>
                        <div>
                            <input type="time" name="deadline_time" id="deadline_time"
                                   value="23:59"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <p class="text-xs text-gray-600 mt-1">Jam</p>
                        </div>
                    </div>
                    <input type="hidden" name="deadline" id="deadline_combined">
                    <p class="text-xs text-gray-600 mt-1">Kosongkan jika tidak ada deadline.</p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" id="btn-cancel-modal"
                            class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2.5 rounded-xl border border-white/10 transition-colors">Batal</button>
                    <button type="submit"
                            class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Buat Tugas</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function() {
        var modal = document.getElementById('modal-tugas');
        function open()  { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        function close() { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        document.getElementById('btn-tambah-tugas')?.addEventListener('click', open);
        document.getElementById('btn-close-modal')?.addEventListener('click', close);
        document.getElementById('btn-cancel-modal')?.addEventListener('click', close);
        modal?.addEventListener('click', function(e) { if (e.target === modal) close(); });

        // Gabungkan tanggal + jam ke hidden input deadline
        function combineDeadline() {
            var d = document.getElementById('deadline_date')?.value;
            var t = document.getElementById('deadline_time')?.value || '23:59';
            var combined = document.getElementById('deadline_combined');
            if (combined) combined.value = d ? (d + ' ' + t + ':00') : '';
        }
        document.getElementById('deadline_date')?.addEventListener('change', combineDeadline);
        document.getElementById('deadline_time')?.addEventListener('change', combineDeadline);
    })();
    </script>

</x-simans-layout>