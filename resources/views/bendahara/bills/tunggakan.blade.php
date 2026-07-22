<x-simans-layout title="Daftar Tunggakan">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Daftar Tunggakan</h1>
            <p class="text-gray-500 text-sm mt-0.5">Siswa yang masih memiliki tagihan belum lunas</p>
        </div>
        <a href="{{ route('bendahara.bills.create') }}"
            class="text-xs bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 px-3 py-2 rounded-lg transition-colors">
            Buat Tagihan Baru
        </a>
    </div>

    {{-- Total tunggakan --}}
    <div class="grid grid-cols-2 gap-4 mb-5">
        <div class="bg-white border border-red-200 rounded-xl px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Total Tunggakan Keseluruhan</p>
            <p class="text-2xl font-bold text-red-600">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Jumlah Siswa Menunggak</p>
            <p class="text-2xl font-bold text-gray-900">{{ $students->total() }} siswa</p>
        </div>
    </div>

    {{-- Filter --}}
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
            <a href="{{ route('bendahara.bills.tunggakan') }}" class="text-gray-500 hover:text-blue-600 text-sm px-3 py-2">Reset</a>
        @endif
    </form>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($students->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-green-600 font-medium">Tidak ada tunggakan!</p>
                <p class="text-gray-500 text-sm mt-1">Semua siswa sudah melunasi tagihan di tahun ajaran ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Siswa</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Kelas</th>
                            <th class="text-center text-xs text-gray-500 font-medium px-4 py-3">Jumlah Tagihan</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Total Tunggakan</th>
                            <th class="text-center text-xs text-gray-500 font-medium px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($students as $student)
                        @php
                            $summary  = $summaries[$student->id] ?? null;
                            $tunggakan = $summary ? (int) $summary->total_tunggakan : 0;
                            $classroom = $student->classrooms->first();
                        @endphp
                        <tr class="hover:bg-white/2 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-gray-900 font-medium">{{ $student->name }}</p>
                                <p class="text-xs text-gray-500">{{ $student->nis ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $classroom?->name ?? '-' }}
                                @if($classroom?->major) / {{ $classroom->major->name }} @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs bg-red-50 text-red-600 border border-red-200 px-2 py-0.5 rounded-full">
                                    {{ $summary?->jumlah_tagihan ?? 0 }} tagihan
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-red-600">
                                Rp {{ number_format($tunggakan, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('bendahara.bills.student', $student) }}?year={{ $yearId }}"
                                    class="text-xs bg-emerald-700 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg transition-colors">
                                    Bayar →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    {{-- Footer total --}}
                    <tfoot>
                        <tr class="border-t border-gray-200 bg-gray-50">
                            <td colspan="3" class="px-4 py-3 text-xs text-gray-500 font-medium">
                                Total {{ $students->total() }} siswa menunggak (halaman ini: {{ $students->count() }})
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-red-600">
                                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $students->links() }}
            </div>
        @endif
    </div>

</x-simans-layout>
