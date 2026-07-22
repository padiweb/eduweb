<x-simans-layout title="Jadwal per Guru">

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.schedules.index') }}"
               class="flex items-center gap-1 text-gray-500 hover:text-blue-600 text-sm mb-2 transition-colors w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Jadwal per Guru</h1>
            <p class="text-gray-500 text-sm mt-1">Lihat semua mapel dan kelas yang diajar satu guru</p>
        </div>
    </div>

    {{-- Pilih Guru --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6">
        <form method="GET" class="flex items-center gap-3">
            <label class="text-sm text-gray-500 flex-shrink-0">Pilih Guru:</label>
            <select name="teacher_id" onchange="this.form.submit()"
                    class="flex-1 bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                <option value="">-- Pilih guru --</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ $teacherId == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if($teacherId && $schedules->isNotEmpty())
        @php $days = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu']; @endphp
        <div class="space-y-4">
            @foreach($days as $dayNum => $dayName)
                @if($schedules->has($dayNum))
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                        <div class="px-5 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">{{ $dayName }}</h3>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @foreach($schedules[$dayNum]->sortBy('start_time') as $s)
                                <div class="flex items-center gap-4 px-5 py-3.5">
                                    <div class="text-xs text-gray-500 flex-shrink-0 w-20 text-center">
                                        {{ substr($s->start_time, 0, 5) }} — {{ substr($s->end_time, 0, 5) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ $s->subject->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $s->classroom->name }}
                                            @if($s->room) &middot; {{ $s->room }} @endif
                                        </p>
                                    </div>
                                    <span class="text-xs text-blue-400 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-full flex-shrink-0">
                                        {{ $s->subject->code ?? $s->subject->category }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Ringkasan mapel yang diajar --}}
        <div class="mt-4 bg-white border border-gray-200 rounded-xl p-5">
            <p class="text-xs font-semibold text-gray-500 mb-3">Ringkasan</p>
            @php
                $uniqueSubjects = $schedules->flatten()->unique('subject_id')->pluck('subject');
                $uniqueClasses  = $schedules->flatten()->unique('classroom_id')->pluck('classroom');
            @endphp
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 mb-2">Mata Pelajaran yang Diajar</p>
                    @foreach($uniqueSubjects as $subj)
                        <p class="text-sm text-gray-900">{{ $subj->name }}</p>
                    @endforeach
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-2">Kelas yang Diajar</p>
                    @foreach($uniqueClasses as $cls)
                        <p class="text-sm text-gray-900">{{ $cls->name }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    @elseif($teacherId)
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-500 text-sm">Guru ini belum punya jadwal mengajar.</p>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-500 text-sm">Pilih guru di atas untuk melihat jadwalnya.</p>
        </div>
    @endif

</x-simans-layout>
