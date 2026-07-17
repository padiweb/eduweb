<x-simans-layout title="Daftar Tunggakan">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-white">Daftar Tunggakan</h1>
            <p class="text-gray-400 text-sm mt-0.5">Siswa yang masih memiliki tagihan belum lunas</p>
        </div>
        <a href="{{ route('bendahara.bills.create') }}"
            class="text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 border border-white/10 px-3 py-2 rounded-lg transition-colors">
            Buat Tagihan Baru
        </a>
    </div>

    {{-- Total tunggakan --}}
    <div class="grid grid-cols-2 gap-4 mb-5">
        <div class="bg-gray-900 border border-red-500/20 rounded-xl px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Total Tunggakan Keseluruhan</p>
            <p class="text-2xl font-bold text-red-400">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
        </div>
        <div class="bg-gray-900 border border-white/5 rounded-xl px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Jumlah Siswa Menunggak</p>
            <p class="text-2xl font-bold text-white">{{ $students->total() }} siswa</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIS siswa..."
            class="bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 w-44 focus:border-purple-500 focus:outline-none">
        <select name="year" class="bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
            @foreach($academicYears as $y)
                <option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>
                    {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' (Aktif)' : '' }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['search','year']))
            <a href="{{ route('bendahara.bills.tunggakan') }}" class="text-gray-400 hover:text-white text-sm px-3 py-2">Reset</a>
        @endif
    </form>

    <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
        @if($students->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-green-400 font-medium">Tidak ada tunggakan!</p>
                <p class="text-gray-500 text-sm mt-1">Semua siswa sudah melunasi tagihan di tahun ajaran ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Siswa</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Kelas</th>
                            <th class="text-center text-xs text-gray-500 font-medium px-4 py-3">Jumlah Tagihan</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Total Tunggakan</th>
                            <th class="text-center text-xs text-gray-500 font-medium px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($students as $student)
                        @php
                            $summary  = $summaries[$student->id] ?? null;
                            $tunggakan = $summary ? (int) $summary->total_tunggakan : 0;
                            $classroom = $student->classrooms->first();
                        @endphp
                        <tr class="hover:bg-white/2 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-white font-medium">{{ $student->name }}</p>
                                <p class="text-xs text-gray-500">{{ $student->nis ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">
                                {{ $classroom?->name ?? '-' }}
                                @if($classroom?->major) / {{ $classroom->major->name }} @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs bg-red-500/10 text-red-400 border border-red-500/20 px-2 py-0.5 rounded-full">
                                    {{ $summary?->jumlah_tagihan ?? 0 }} tagihan
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-red-400">
                                Rp {{ number_format($tunggakan, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('bendahara.bills.student', $student) }}?year={{ $yearId }}"
                                    class="text-xs bg-emerald-700 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg transition-colors">
                                    Bayar →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    {{-- Footer total --}}
                    <tfoot>
                        <tr class="border-t border-white/10 bg-gray-800/50">
                            <td colspan="3" class="px-4 py-3 text-xs text-gray-400 font-medium">
                                Total {{ $students->total() }} siswa menunggak (halaman ini: {{ $students->count() }})
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-red-400">
                                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-white/5">
                {{ $students->links() }}
            </div>
        @endif
    </div>

</x-simans-layout>
