<x-simans-layout title="Isi Jurnal Mengajar">

    <div class="mb-6">
        <a href="{{ route('guru.journal.index') }}"
           class="flex items-center gap-1 text-gray-500 hover:text-gray-900 text-sm mb-2 transition-colors w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $existing ? 'Edit' : 'Isi' }} Jurnal Mengajar
        </h1>
        <p class="text-gray-500 text-sm mt-1">
            {{ $schedule->subject->name }} — {{ $schedule->classroom->name }}
            &middot; {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
        </p>
    </div>

    @if($errors->any())
        <div class="mb-5 bg-red-900/30 border border-red-700/40 text-red-300 px-4 py-3 rounded-xl text-sm">
            <ul class="space-y-1">
                @foreach($errors->all() as $err)
                    <li>&bull; {{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Info jadwal --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-5 flex items-center gap-4">
        <div class="text-center flex-shrink-0 w-14">
            <p class="text-sm font-bold text-gray-900">{{ substr($schedule->start_time, 0, 5) }}</p>
            <p class="text-xs text-gray-400">↓</p>
            <p class="text-sm text-gray-500">{{ substr($schedule->end_time, 0, 5) }}</p>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-gray-900">{{ $schedule->subject->name }}</p>
            <p class="text-xs text-gray-400">
                {{ $schedule->classroom->name }}
                @if($schedule->room) &middot; {{ $schedule->room }} @endif
                &middot; {{ $schedule->classroom->students->count() }} siswa
            </p>
        </div>
        <div class="flex-shrink-0 text-right">
            <p class="text-xs text-gray-400">Pertemuan ke-</p>
            <p class="text-2xl font-bold text-blue-600">{{ $meetingNumber }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('guru.journal.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
        <input type="hidden" name="journal_date" value="{{ $date }}">
        <input type="hidden" name="meeting_number" value="{{ $meetingNumber }}">

        <div class="space-y-5">

            {{-- Materi & metode --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
                <h2 class="text-sm font-semibold text-gray-900">Materi & Metode</h2>

                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Topik / Materi <span class="text-red-400">*</span></label>
                    <input type="text" name="topic" required
                           value="{{ old('topic', $existing?->topic ?? '') }}"
                           placeholder="cth: Pengenalan Jaringan Komputer, Integral Parsial"
                           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Uraian Kegiatan (opsional)</label>
                    <textarea name="description" rows="3"
                              placeholder="Uraian singkat kegiatan pembelajaran hari ini..."
                              class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 resize-none transition-colors">{{ old('description', $existing?->description ?? '') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Metode Pembelajaran <span class="text-red-400">*</span></label>
                    <select name="method" required
                            class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        @foreach([
                            'ceramah'     => 'Ceramah',
                            'diskusi'     => 'Diskusi',
                            'praktek'     => 'Praktik',
                            'demonstrasi' => 'Demonstrasi',
                            'presentasi'  => 'Presentasi',
                            'tanya_jawab' => 'Tanya Jawab',
                            'lainnya'     => 'Lainnya',
                        ] as $val => $label)
                            <option value="{{ $val }}"
                                {{ old('method', $existing?->method ?? 'ceramah') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Kehadiran siswa --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Kehadiran Siswa</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Siswa Hadir <span class="text-red-400">*</span></label>
                        <input type="number" name="students_present" min="0"
                               value="{{ old('students_present', $existing?->students_present ?? $schedule->classroom->students->count()) }}"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        <p class="text-xs text-gray-400 mt-1">Total: {{ $schedule->classroom->students->count() }} siswa</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Siswa Tidak Hadir</label>
                        <input type="number" name="students_absent" min="0"
                               value="{{ old('students_absent', $existing?->students_absent ?? 0) }}"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                    </div>
                </div>
            </div>

            {{-- Foto & catatan --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
                <h2 class="text-sm font-semibold text-gray-900">Foto & Catatan</h2>

                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">
                        Foto Kegiatan (opsional)
                    </label>
                    @if($existing?->photo_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/'.$existing->photo_path) }}"
                                 alt="Foto jurnal"
                                 class="h-32 rounded-xl object-cover border border-gray-200">
                        </div>
                    @endif
                    <input type="file" name="photo" accept="image/*"
                           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none transition-colors file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-gray-100 file:text-gray-600">
                    <p class="text-xs text-gray-400 mt-1">JPG/PNG, maks 3MB. Foto suasana kelas / kegiatan belajar.</p>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Catatan (opsional)</label>
                    <textarea name="notes" rows="2"
                              placeholder="cth: Siswa aktif berdiskusi, perlu remedial untuk 3 siswa..."
                              class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 resize-none transition-colors">{{ old('notes', $existing?->notes ?? '') }}</textarea>
                </div>
            </div>

            {{-- Info poin --}}
            @if(! $existing?->is_reward_given)
                <div class="flex items-center gap-3 bg-emerald-900/20 border border-blue-200 rounded-xl px-4 py-3">
                    <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                    </svg>
                    <p class="text-xs text-blue-700">Mengisi jurnal akan memberikan <span class="font-bold">+1 poin reward</span>.</p>
                </div>
            @endif

            {{-- Tombol --}}
            <div class="flex gap-3 justify-end">
                <a href="{{ route('guru.journal.index') }}"
                   class="px-6 py-2.5 text-sm font-medium rounded-xl bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 text-sm font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-gray-900 transition-colors">
                    {{ $existing ? 'Simpan Perubahan' : 'Simpan Jurnal' }}
                </button>
            </div>
        </div>
    </form>

</x-simans-layout>
