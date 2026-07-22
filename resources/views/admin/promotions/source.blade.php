<x-simans-layout title="Promosi Siswa">

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.promotions.index') }}"
               class="flex items-center gap-1 text-gray-500 hover:text-gray-900 text-sm mb-2 transition-colors w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Promosi Massal</h1>
            <p class="text-gray-500 text-sm mt-1">
                Tujuan: <span class="text-blue-600 font-semibold">{{ $activeYear?->label ?? 'Belum ada tahun ajaran aktif' }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Tombol set semua naik kelas --}}
            <button type="button"
                    onclick="setAllStudents('naik')"
                    class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 bg-blue-600/10 hover:bg-blue-600/20 border border-blue-200 px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/>
                </svg>
                Semua Naik Kelas
            </button>
            <button form="form-promosi" type="submit"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-gray-900 text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors"
                    onclick="return confirm('Proses promosi massal? Pastikan semua pilihan sudah benar.')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Proses Promosi
            </button>
        </div>
    </div>

    @if($classrooms->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-400 text-sm">Tidak ada kelas aktif di tahun ajaran yang dipilih.</p>
        </div>
    @else
        {{-- Tombol global --}}
        <div class="flex items-center gap-3 mb-4 p-4 bg-white border border-gray-200 rounded-xl">
            <span class="text-xs text-gray-500 font-semibold">Set semua siswa:</span>
            <button type="button" onclick="setAllStudents('naik')"
                    class="text-xs text-blue-600 bg-blue-600/10 border border-blue-200 hover:bg-blue-600/20 px-3 py-1.5 rounded-lg transition-colors">
                ⬆ Semua Naik Kelas
            </button>
            <button type="button" onclick="setAllStudents('lulus')"
                    class="text-xs text-blue-400 bg-blue-500/10 border border-blue-500/20 hover:bg-blue-500/20 px-3 py-1.5 rounded-lg transition-colors">
                🎓 Semua Lulus
            </button>
        </div>

        <form id="form-promosi" method="POST" action="{{ route('admin.promotions.process') }}">
            @csrf

            @foreach($classrooms as $classroom)
                @if($classroom->students->count() === 0) @continue @endif

                <div class="mb-6 bg-white border border-gray-200 rounded-xl overflow-hidden">
                    {{-- Header kelas --}}
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">{{ $classroom->name }}</h2>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Kelas {{ $classroom->grade }}
                                @if($classroom->major) &middot; {{ $classroom->major->name }} @endif
                                &middot; {{ $classroom->students->count() }} siswa aktif
                            </p>
                        </div>
                        {{-- Tombol set semua naik kelas --}}
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    onclick="setAllInClass('{{ $classroom->id }}', 'naik', {{ $classroom->grade }})"
                                    class="text-xs text-blue-600 hover:text-blue-700 bg-blue-600/10 border border-blue-200 px-3 py-1.5 rounded-lg transition-colors">
                                ⬆ Semua Naik Kelas
                            </button>
                            @if($classroom->grade >= ($school->school_program_years * 2 / 2 + 9))
                                <button type="button"
                                        onclick="setAllInClass('{{ $classroom->id }}', 'lulus', {{ $classroom->grade }})"
                                        class="text-xs text-blue-400 hover:text-blue-300 bg-blue-500/10 border border-blue-500/20 px-3 py-1.5 rounded-lg transition-colors">
                                    🎓 Semua Lulus
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Daftar siswa --}}
                    <div class="divide-y divide-gray-100">
                        @foreach($classroom->students as $i => $student)
                            @php $idx = $classroom->id . '_' . $student->id; @endphp
                            <input type="hidden" name="promotions[{{ $idx }}][student_id]" value="{{ $student->id }}">

                            <div class="px-5 py-3.5 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center" data-class="{{ $classroom->id }}">
                                {{-- Nama siswa --}}
                                <div class="sm:col-span-3 flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0">
                                        <img src="{{ $student->avatarUrl }}" class="w-full h-full object-cover" alt="">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $student->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $student->nis ?? $student->nisn ?? '-' }}</p>
                                    </div>
                                </div>

                                {{-- Pilih aksi --}}
                                <div class="sm:col-span-3">
                                    <select name="promotions[{{ $idx }}][action]"
                                            class="action-select w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                            data-idx="{{ $idx }}"
                                            data-classid="{{ $classroom->id }}"
                                            onchange="handleActionChange(this)">
                                        <option value="naik">⬆ Naik Kelas</option>
                                        <option value="tidak_naik">↩ Tidak Naik</option>
                                        <option value="lulus">🎓 Lulus / Tamat</option>
                                        <option value="keluar">✖ Keluar / DO</option>
                                        <option value="pindah">➡ Pindah Sekolah</option>
                                    </select>
                                </div>

                                {{-- Pilih kelas tujuan --}}
                                <div class="sm:col-span-4 target-class-wrap-{{ $idx }}">
                                    <select name="promotions[{{ $idx }}][target_class]"
                                            class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                        <option value="">-- Pilih kelas tujuan --</option>
                                        @foreach($targetClassrooms->sortBy('grade') as $tc)
                                            <option value="{{ $tc->id }}">
                                                {{ $tc->name }} (Kelas {{ $tc->grade }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Catatan --}}
                                <div class="sm:col-span-2">
                                    <input type="text" name="promotions[{{ $idx }}][notes]"
                                           placeholder="Catatan..."
                                           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </form>

        {{-- Tombol proses di bawah --}}
        <div class="flex justify-end mt-4">
            <button form="form-promosi" type="submit"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-gray-900 text-sm font-semibold px-6 py-3 rounded-xl transition-colors"
                    onclick="return confirm('Proses promosi massal? Pastikan semua pilihan sudah benar.')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Proses Promosi Massal
            </button>
        </div>
    @endif

    <script>
    // Data kelas tujuan untuk auto-match berdasarkan grade
    var targetClassrooms = @json($targetClassrooms->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'grade' => $c->grade])->values());

    function handleActionChange(select) {
        var idx    = select.dataset.idx;
        var wrap   = document.querySelector('.target-class-wrap-' + idx);
        var action = select.value;
        if (wrap) {
            wrap.style.display = (action === 'naik' || action === 'tidak_naik') ? '' : 'none';
        }
    }

    // Set semua siswa dalam satu kelas + auto-pilih kelas tujuan
    function setAllInClass(classId, action, sourceGrade) {
        var selects = document.querySelectorAll('.action-select[data-classid="' + classId + '"]');
        selects.forEach(function(sel) {
            sel.value = action;
            handleActionChange(sel);

            // Auto-pilih kelas tujuan dengan grade lebih tinggi
            if ((action === 'naik' || action === 'tidak_naik') && sourceGrade) {
                var idx     = sel.dataset.idx;
                var wrap    = document.querySelector('.target-class-wrap-' + idx);
                if (wrap) {
                    var targetSel = wrap.querySelector('select');
                    if (targetSel && !targetSel.value) {
                        var targetGrade = action === 'naik' ? parseInt(sourceGrade) + 1 : parseInt(sourceGrade);
                        // Cari kelas tujuan dengan grade yang cocok
                        var matched = targetClassrooms.find(function(tc) { return tc.grade === targetGrade; });
                        if (matched) targetSel.value = matched.id;
                    }
                }
            }
        });

        var count = selects.length;
        // Tampilkan konfirmasi singkat
        var btn = event.target;
        var orig = btn.textContent;
        btn.textContent = '✓ ' + count + ' siswa';
        btn.className = btn.className.replace('emerald', 'blue');
        setTimeout(function() {
            btn.textContent = orig;
            btn.className = btn.className.replace('blue', 'emerald');
        }, 2000);
    }

    // Set SEMUA siswa di semua kelas
    function setAllStudents(action) {
        document.querySelectorAll('.action-select').forEach(function(sel) {
            sel.value = action;
            handleActionChange(sel);
        });
    }

    // Inisialisasi saat halaman load
    document.querySelectorAll('.action-select').forEach(handleActionChange);
    </script>

</x-simans-layout>
