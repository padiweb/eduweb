<x-simans-layout title="Manajemen Pelanggaran">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Pelanggaran</h1>
            <p class="text-gray-500 text-sm mt-1">Daftar siswa diurutkan berdasarkan poin pelanggaran tertinggi</p>
        </div>
        <a href="{{ route('kesiswaan.violations.categories') }}"
           class="flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
            </svg>
            Kelola Kategori
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-900">Semua Siswa</h2>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($students as $student)
                @php
                    $pts   = $student->total_points ?? 0;
                    $color = $pts >= 20 ? 'red' : ($pts >= 10 ? 'amber' : 'gray');
                @endphp
                <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition-colors">

                    {{-- Avatar --}}
                    <div class="w-9 h-9 rounded-full bg-white flex items-center justify-center text-xs font-bold text-gray-600 flex-shrink-0">
                        {{ substr($student->name, 0, 2) }}
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $student->name }}</p>
                        <p class="text-xs text-gray-500">
                            NIS: {{ $student->nis }}
                            @if($student->classrooms->first())
                                · {{ $student->classrooms->first()->name }}
                            @endif
                        </p>
                    </div>

                    {{-- Jumlah pelanggaran --}}
                    <span class="text-xs text-gray-500 flex-shrink-0">
                        {{ $student->violation_count ?? 0 }} pelanggaran
                    </span>

                    {{-- Total poin --}}
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-{{ $color }}-500/10 border border-{{ $color }}-500/20 flex-shrink-0">
                        <span class="text-sm font-bold text-{{ $color }}-400">{{ $pts }}</span>
                    </div>

                    {{-- Lihat detail --}}
                    <a href="{{ route('kesiswaan.violations.show', $student->id) }}"
                       class="text-xs text-blue-600 hover:text-blue-700 font-medium transition-colors flex-shrink-0">
                        Detail →
                    </a>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <p class="text-gray-500 text-sm">Belum ada data siswa.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($students->hasPages())
            <div class="px-5 py-4 border-t border-gray-200">
                {{ $students->links() }}
            </div>
        @endif
    </div>

</x-simans-layout>
