<x-simans-layout title="Riwayat Jurnal Mengajar">

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('guru.journal.index') }}"
               class="flex items-center gap-1 text-gray-500 hover:text-gray-900 text-sm mb-2 transition-colors w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Jurnal Mengajar</h1>
        </div>
    </div>

    {{-- Pilih kelas & mapel --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6">
        <form method="GET" class="flex items-center gap-3">
            <label class="text-sm text-gray-500 flex-shrink-0">Kelas & Mapel:</label>
            <select name="schedule_id" onchange="this.form.submit()"
                    class="flex-1 bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                <option value="">-- Pilih kelas & mapel --</option>
                @foreach($schedules as $s)
                    <option value="{{ $s->id }}" {{ $scheduleId == $s->id ? 'selected' : '' }}>
                        {{ $s->classroom->name }} — {{ $s->subject->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($selectedSchedule)
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">
                        {{ $selectedSchedule->classroom->name ?? '' }} — {{ $selectedSchedule->subject->name ?? '' }}
                    </h2>
                </div>
                <span class="text-xs text-gray-500">{{ $journals->count() }} jurnal</span>
            </div>

            @if($journals->isEmpty())
                <div class="px-5 py-10 text-center">
                    <p class="text-gray-500 text-sm">Belum ada jurnal untuk kelas & mapel ini.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($journals as $j)
                        <div class="flex items-start gap-4 px-5 py-4">
                            <div class="text-center flex-shrink-0 w-12">
                                <p class="text-xs text-gray-500">Ptm</p>
                                <p class="text-xl font-bold text-blue-600">{{ $j->meeting_number }}</p>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $j->topic }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $j->journal_date->translatedFormat('d F Y') }}
                                    &middot; {{ $j->methodLabel }}
                                    &middot; {{ $j->students_present }} hadir
                                </p>
                                @if($j->description)
                                    <p class="text-xs text-gray-500 mt-1 truncate">{{ $j->description }}</p>
                                @endif
                            </div>
                            @if($j->photo_path)
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('storage/'.$j->photo_path) }}"
                                         alt="Foto"
                                         class="w-14 h-14 rounded-xl object-cover border border-gray-200">
                                </div>
                            @endif
                            <div class="flex items-center gap-1 flex-shrink-0">
                                @if($j->is_reward_given)
                                    <span class="text-xs text-blue-600" title="+1 poin reward">★</span>
                                @endif
                                <a href="{{ route('guru.journal.create', ['schedule_id' => $j->schedule_id, 'date' => $j->journal_date->format('Y-m-d')]) }}"
                                   class="w-7 h-7 flex items-center justify-center rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-xl p-10 text-center">
            <p class="text-gray-500 text-sm">Pilih kelas & mapel di atas untuk melihat riwayat jurnal.</p>
        </div>
    @endif

</x-simans-layout>
