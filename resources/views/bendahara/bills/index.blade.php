<x-simans-layout title="Kelola Tagihan">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Kelola Tagihan</h1>
            <p class="text-gray-500 text-sm mt-0.5">Daftar siswa — klik untuk lihat dan bayar tagihan</p>
        </div>
        <a href="{{ route('bendahara.bills.create') }}"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buat Tagihan
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-600 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif

    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIS siswa..."
            class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 w-44 focus:border-blue-500 focus:outline-none">
        <select name="year" class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
            @foreach($academicYears as $y)
                <option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>
                    {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' (Aktif)' : '' }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['search','year']))
            <a href="{{ route('bendahara.bills.index') }}" class="text-gray-500 hover:text-blue-600 text-sm px-3 py-2">Reset</a>
        @endif
    </form>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($students->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-500">Belum ada siswa dengan tagihan di tahun ajaran ini.</p>
                <a href="{{ route('bendahara.bills.create') }}" class="text-blue-600 text-sm mt-2 inline-block hover:text-blue-500">Buat tagihan baru</a>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($students as $student)
                @php
                    $summary   = $billSummaries[$student->id] ?? null;
                    $remaining = $summary ? (int) $summary->total_remaining : 0;
                    $paid      = $summary ? (int) $summary->total_paid : 0;
                    $total     = $summary ? (int) $summary->total_billed : 0;
                    $classroom = $student->classrooms->first();
                @endphp
                <a href="{{ route('bendahara.bills.student', $student) }}?year={{ $yearId }}"
                    class="flex items-center gap-4 px-5 py-4 hover:bg-white/2 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-white border border-gray-200 flex items-center justify-center text-sm font-semibold text-gray-600 flex-shrink-0">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $student->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $student->nis ?? '-' }}
                            @if($classroom)
                                · {{ $classroom->name }}{{ $classroom->major ? ' / ' . $classroom->major->name : '' }}
                            @endif
                        </p>
                    </div>
                    @if($summary)
                    <div class="hidden md:flex items-center gap-6 text-right shrink-0">
                        <div>
                            <p class="text-xs text-gray-500">Total</p>
                            <p class="text-sm text-gray-500">Rp {{ number_format($total, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Dibayar</p>
                            <p class="text-sm text-green-600">Rp {{ number_format($paid, 0, ',', '.') }}</p>
                        </div>
                        <div class="min-w-28">
                            <p class="text-xs text-gray-500">Sisa</p>
                            <p class="text-sm font-semibold {{ $remaining > 0 ? 'text-red-600' : 'text-gray-500' }}">
                                {{ $remaining > 0 ? 'Rp ' . number_format($remaining, 0, ',', '.') : 'Lunas' }}
                            </p>
                        </div>
                    </div>
                    @endif
                    <div class="flex items-center gap-2 shrink-0">
                        @if($remaining > 0)
                            <span class="text-xs bg-red-50 text-red-600 border border-red-200 px-2 py-0.5 rounded-full">Tunggakan</span>
                        @elseif($summary)
                            <span class="text-xs bg-green-50 text-green-600 border border-green-200 px-2 py-0.5 rounded-full">Lunas</span>
                        @endif
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </div>
                </a>
                @endforeach
            </div>
            <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between">
                <p class="text-xs text-gray-500">{{ $students->total() }} siswa</p>
                {{ $students->links() }}
            </div>
        @endif
    </div>

</x-simans-layout>
