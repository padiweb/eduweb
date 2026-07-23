<x-simans-layout title="Poin Reward Saya">

    <div class="mb-6">
        <a href="{{ route('guru.teacher-attendance.index') }}"
           class="flex items-center gap-1 text-gray-500 hover:text-blue-600 text-sm mb-2 transition-colors w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Poin Reward Saya</h1>
        <p class="text-gray-500 text-sm mt-1">Poin terkumpul dari absensi tepat waktu dan jurnal mengajar</p>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $summary['this_month'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Bulan Ini</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-blue-400">{{ $summary['this_year'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Tahun Ini</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $summary['total'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Total</p>
        </div>
    </div>

    {{-- Riwayat poin --}}
    <div class="tbl-card">
        <div class="px-5 py-4 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-900">Riwayat Poin</h2>
        </div>
        @if($points->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-500 text-sm">Belum ada poin reward.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($points as $pt)
                    @php
                        $isPositive = $pt->points > 0;
                        $typeColors = [
                            'absen_tepat_waktu' => 'emerald',
                            'isi_jurnal'        => 'blue',
                            'bonus'             => 'amber',
                            'pengurang'         => 'red',
                        ];
                        $c = $typeColors[$pt->type] ?? 'gray';
                    @endphp
                    <div class="flex items-center gap-3 px-5 py-3.5">
                        <div class="w-8 h-8 rounded-full bg-{{ $c }}-500/10 border border-{{ $c }}-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-bold text-{{ $c }}-400">{{ $isPositive ? '+' : '' }}{{ $pt->points }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $pt->typeLabel }}</p>
                            @if($pt->description)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $pt->description }}</p>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 flex-shrink-0">{{ $pt->point_date->translatedFormat('d M Y') }}</p>
                    </div>
                @endforeach
            </div>
            <div class="px-5 py-4 border-t border-gray-200">{{ $points->links() }}</div>
        @endif
    </div>

</x-simans-layout>