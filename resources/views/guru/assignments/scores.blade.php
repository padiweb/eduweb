<x-simans-layout title="Rekap Nilai">

    <div class="mb-6">
        <a href="{{ route('guru.assignments.index') }}"
           class="flex items-center gap-1 text-gray-500 hover:text-blue-600 text-sm mb-2 transition-colors w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Rekap Nilai</h1>
        <p class="text-gray-500 text-sm mt-1">Rata-rata nilai tugas per siswa per mata pelajaran</p>
    </div>

    <form method="GET" class="tab-nav-scroll">
        <select name="classroom_id"
                class="bg-white border border-gray-200 text-gray-900 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition-colors">
            <option value="">Pilih Kelas...</option>
            @foreach($classrooms as $c)
                <option value="{{ $c->id }}" {{ $classroomId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="subject_id"
                class="bg-white border border-gray-200 text-gray-900 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition-colors">
            <option value="">Pilih Mata Pelajaran...</option>
            @foreach($subjects as $s)
                <option value="{{ $s->id }}" {{ $subjectId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            Tampilkan
        </button>
    </form>

    @if($data)
        @if($data['assignments']->count() > 0)
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 min-w-[180px]">Nama Siswa</th>
                            @foreach($data['assignments'] as $a)
                                <th class="px-3 py-3.5 text-center text-xs font-semibold text-gray-500 min-w-[90px]">
                                    <span class="block truncate max-w-[80px] mx-auto" title="{{ $a->title }}">
                                        {{ Str::limit($a->title, 15) }}
                                    </span>
                                </th>
                            @endforeach
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-amber-600 min-w-[80px]">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($data['students'] as $row)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-900">{{ $row['student']->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $row['student']->nis }}</p>
                                </td>
                                @foreach($data['assignments'] as $a)
                                    @php
                                        $cell   = $row['scores'][$a->id] ?? null;
                                        $score  = is_array($cell) ? $cell['score'] : $cell;
                                        $status = is_array($cell) ? $cell['status'] : null;
                                    @endphp
                                    <td class="px-3 py-3 text-center">
                                        @if($status === 'not_submitted')
                                            <span class="text-xs text-red-500" title="Tidak Dikumpulkan">TK</span>
                                        @elseif($score !== null)
                                            <span class="font-semibold {{ $score >= 80 ? 'text-blue-600' : ($score >= 70 ? 'text-blue-400' : ($score >= 60 ? 'text-amber-600' : 'text-red-600')) }}">
                                                {{ $score }}
                                            </span>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-5 py-3 text-center">
                                    @if($row['average'] !== null)
                                        <span class="font-bold text-lg {{ $row['average'] >= 80 ? 'text-blue-600' : ($row['average'] >= 70 ? 'text-blue-400' : ($row['average'] >= 60 ? 'text-amber-600' : 'text-red-600')) }}">
                                            {{ $row['average'] }}
                                        </span>
                                    @else
                                        <span class="text-gray-500 text-base">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500 mt-2">TK = Tidak Dikumpulkan (tidak dihitung dalam rata-rata)</p>
        @else
            <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
                <p class="text-gray-500 text-sm">Belum ada tugas yang ditutup untuk kelas dan mapel ini.</p>
            </div>
        @endif
    @else
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-500 text-sm">Pilih kelas dan mata pelajaran untuk melihat rekap nilai.</p>
        </div>
    @endif

</x-simans-layout>