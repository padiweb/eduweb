<x-simans-layout title="Kelola Tagihan">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-white">Kelola Tagihan</h1>
            <p class="text-gray-400 text-sm mt-0.5">Daftar siswa dengan tagihan aktif</p>
        </div>
        <a href="{{ route('bendahara.bills.create') }}"
            class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buat Tagihan
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIS..."
            class="bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 w-44 focus:border-purple-500 focus:outline-none">
        <select name="year" class="bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
            @foreach($academicYears as $y)
                <option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>
                    {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' ✓' : '' }}
                </option>
            @endforeach
        </select>
        <select name="status" class="bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
            <option value="">Semua status</option>
            <option value="unpaid"  {{ request('status')=='unpaid'  ? 'selected':'' }}>Ada tunggakan</option>
            <option value="paid"    {{ request('status')=='paid'    ? 'selected':'' }}>Semua lunas</option>
        </select>
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['search','status','type']))
            <a href="{{ route('bendahara.bills.index') }}" class="text-gray-400 hover:text-white text-sm px-3 py-2">Reset</a>
        @endif
    </form>

    <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
        @if($students->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-500">Belum ada siswa dengan tagihan.</p>
            </div>
        @else
            <div class="divide-y divide-white/5">
                @foreach($students as $student)
                @php
                    $summary   = $billSummaries[$student->id] ?? null;
                    $remaining = $summary ? (int) $summary->total_remaining : 0;
                    $paid      = $summary ? (int) $summary->total_paid : 0;
                    $total     = $summary ? (int) $summary->total_billed : 0;
                    $classroom = $student->classrooms->first();
                @endphp
                <div class="px-5 py-4 flex items-center gap-4 hover:bg-white/2 transition-colors">
                    {{-- Avatar --}}
                    <div class="w-10 h-10 rounded-full bg-purple-900/50 border border-purple-500/20 flex items-center justify-center text-sm font-bold text-purple-300 flex-shrink-0">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>

                    {{-- Info siswa --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white">{{ $student->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $student->nis ?? '-' }}
                            @if($classroom)
                                · {{ $classroom->name }}{{ $classroom->major ? ' - ' . $classroom->major->name : '' }}
                            @endif
                        </p>
                    </div>

                    {{-- Ringkasan keuangan --}}
                    @if($summary)
                    <div class="hidden sm:flex items-center gap-6 text-right shrink-0">
                        <div>
                            <p class="text-xs text-gray-600 mb-0.5">Total tagihan</p>
                            <p class="text-sm text-gray-300">Rp {{ number_format($total, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 mb-0.5">Sudah bayar</p>
                            <p class="text-sm text-green-400">Rp {{ number_format($paid, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 mb-0.5">Sisa</p>
                            <p class="text-sm font-semibold {{ $remaining > 0 ? 'text-red-400' : 'text-gray-400' }}">
                                {{ $remaining > 0 ? 'Rp ' . number_format($remaining, 0, ',', '.') : 'Lunas' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 mb-0.5">Tagihan</p>
                            <p class="text-sm text-gray-400">{{ $summary->total_bills }}x</p>
                        </div>
                    </div>
                    @endif

                    {{-- Status badge + link --}}
                    <div class="flex items-center gap-3 shrink-0">
                        @if($remaining > 0)
                            <span class="text-xs bg-red-500/10 text-red-400 border border-red-500/20 px-2.5 py-1 rounded-full hidden sm:block">Tunggakan</span>
                        @elseif($summary)
                            <span class="text-xs bg-green-500/10 text-green-400 border border-green-500/20 px-2.5 py-1 rounded-full hidden sm:block">Lunas</span>
                        @endif
                        <a href="{{ route('bendahara.bills.student', $student) }}?year={{ $yearId }}"
                            class="text-xs bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors whitespace-nowrap">
                            Detail →
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="px-4 py-3 border-t border-white/5 text-xs text-gray-500">
                {{ $students->total() }} siswa · {{ $students->links() }}
            </div>
        @endif
    </div>

</x-simans-layout>
