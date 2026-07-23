<x-simans-layout title="Jurnal Mengajar">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Jurnal Mengajar</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $today->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('guru.journal.history') }}"
               class="text-sm text-gray-500 hover:text-blue-600 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-colors">
                Riwayat Jurnal
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stat bulan ini --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
            </svg>
        </div>
        <div>
            <p class="text-lg font-bold text-gray-900">{{ $journalThisMonth }} jurnal</p>
            <p class="text-xs text-gray-500">diisi bulan {{ now()->translatedFormat('F Y') }}</p>
        </div>
        <p class="text-xs text-gray-500 ml-auto">+1 poin reward per jurnal</p>
    </div>

    {{-- Jadwal hari ini --}}
    <h2 class="text-sm font-semibold text-gray-900 mb-3">Jadwal Hari Ini</h2>

    @if($todaySchedules->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-10 text-center mb-6">
            <p class="text-gray-500 text-sm">Tidak ada jadwal mengajar hari ini.</p>
        </div>
    @else
        <div class="space-y-3 mb-6">
            @foreach($todaySchedules as $schedule)
                @php $journal = $schedule->today_journal; @endphp
                <div class="bg-white border border-gray-200 rounded-xl p-5 flex items-center gap-4">
                    {{-- Jam --}}
                    <div class="text-center flex-shrink-0 w-16">
                        <p class="text-sm font-bold text-gray-900">{{ substr($schedule->start_time, 0, 5) }}</p>
                        <p class="text-xs text-gray-500">↓</p>
                        <p class="text-sm text-gray-500">{{ substr($schedule->end_time, 0, 5) }}</p>
                    </div>

                    {{-- Info mapel --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900">{{ $schedule->subject->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $schedule->classroom->name }}</p>
                        @if($journal)
                            <p class="text-xs text-blue-600 mt-1">
                                Pertemuan {{ $journal->meeting_number }}: {{ $journal->topic }}
                            </p>
                        @endif
                    </div>

                    {{-- Status & aksi --}}
                    @if($journal)
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-xs text-blue-600 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-full font-semibold">
                                Sudah diisi
                            </span>
                            <a href="{{ route('guru.journal.create', ['schedule_id' => $schedule->id, 'date' => today()->format('Y-m-d')]) }}"
                               class="text-xs text-gray-500 hover:text-blue-600 bg-white border border-gray-200 px-3 py-1.5 rounded-lg transition-colors">
                                Edit
                            </a>
                        </div>
                    @else
                        <a href="{{ route('guru.journal.create', ['schedule_id' => $schedule->id, 'date' => today()->format('Y-m-d')]) }}"
                           class="flex-shrink-0 flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            Isi Jurnal
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Isi jurnal untuk hari lain / jadwal lain --}}
    <div class="tbl-card">
        <div class="px-5 py-4 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-900">Isi Jurnal Hari Lain</h2>
            <p class="text-xs text-gray-500 mt-0.5">Bisa isi jurnal untuk tanggal selain hari ini</p>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Pilih Kelas & Mapel</label>
                    <select id="sel-schedule"
                            class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">-- Pilih jadwal --</option>
                        @foreach($allSchedules as $s)
                            <option value="{{ $s->id }}">
                                {{ $s->classroom->name }} — {{ $s->subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Tanggal</label>
                    <input type="date" id="sel-date" value="{{ today()->format('Y-m-d') }}"
                           max="{{ today()->format('Y-m-d') }}"
                           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                </div>
            </div>
            <div class="mt-3">
                <button onclick="goToJournal()"
                        class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                    Isi Jurnal
                </button>
            </div>
        </div>
    </div>

    <script>
    function goToJournal() {
        var scheduleId = document.getElementById('sel-schedule').value;
        var date       = document.getElementById('sel-date').value;
        if (!scheduleId) { alert('Pilih kelas dan mata pelajaran dulu.'); return; }
        if (!date)       { alert('Pilih tanggal dulu.'); return; }
        window.location.href = '{{ route("guru.journal.create") }}?schedule_id=' + scheduleId + '&date=' + date;
    }
    </script>

</x-simans-layout>
