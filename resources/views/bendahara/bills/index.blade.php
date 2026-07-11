<x-simans-layout title="Daftar Tagihan">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-white">Daftar Tagihan</h1>
            <p class="text-gray-400 text-sm mt-0.5">Kelola semua tagihan pembayaran siswa</p>
        </div>
        <a href="{{ route('bendahara.bills.create') }}"
            class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buat Tagihan
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIS siswa..."
            class="bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 w-48 focus:border-purple-500 focus:outline-none">
        <select name="type" class="bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
            <option value="">Semua jenis</option>
            @foreach($types as $t)
                <option value="{{ $t->id }}" {{ request('type') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
        </select>
        <select name="status" class="bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
            <option value="">Semua status</option>
            <option value="unpaid"  {{ request('status') == 'unpaid'  ? 'selected' : '' }}>Belum bayar</option>
            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Cicilan</option>
            <option value="paid"    {{ request('status') == 'paid'    ? 'selected' : '' }}>Lunas</option>
            <option value="waived"  {{ request('status') == 'waived'  ? 'selected' : '' }}>Dibebaskan</option>
        </select>
        <select name="year" class="bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
            <option value="">Semua tahun</option>
            @foreach($academicYears as $y)
                <option value="{{ $y->id }}" {{ request('year') == $y->id ? 'selected' : '' }}>
                    {{ $y->name }} Sem {{ $y->semester }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['search','type','status','year']))
            <a href="{{ route('bendahara.bills.index') }}" class="text-gray-400 hover:text-white text-sm px-3 py-2 rounded-lg">Reset</a>
        @endif
    </form>

    <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
        @if($bills->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-500">Belum ada tagihan yang sesuai filter.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Siswa</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Jenis</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Periode</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Tagihan</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Terbayar</th>
                            <th class="text-center text-xs text-gray-500 font-medium px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($bills as $bill)
                        @php
                            $colors = ['unpaid'=>'red','partial'=>'amber','paid'=>'green','waived'=>'blue'];
                            $labels = ['unpaid'=>'Belum bayar','partial'=>'Cicilan','paid'=>'Lunas','waived'=>'Dibebaskan'];
                            $c = $colors[$bill->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-white/2 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-white font-medium">{{ $bill->student->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $bill->student->nis ?? $bill->student->username ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-300">{{ $bill->paymentType->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <p class="text-gray-300">{{ $bill->period_label }}</p>
                                @if($bill->due_date)
                                    <p class="text-xs {{ \Carbon\Carbon::parse($bill->due_date)->isPast() && $bill->status !== 'paid' ? 'text-red-400' : 'text-gray-500' }}">
                                        JT {{ \Carbon\Carbon::parse($bill->due_date)->format('d/m/Y') }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <p class="text-white font-medium">Rp {{ number_format($bill->amount_billed, 0, ',', '.') }}</p>
                                @if($bill->amount_discount > 0)
                                    <p class="text-xs text-green-400">-Rp {{ number_format($bill->amount_discount, 0, ',', '.') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <p class="text-green-400">Rp {{ number_format($bill->amount_paid, 0, ',', '.') }}</p>
                                @if($bill->amount_remaining > 0 && $bill->status !== 'waived')
                                    <p class="text-xs text-red-400">Sisa Rp {{ number_format($bill->amount_remaining, 0, ',', '.') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs bg-{{ $c }}-500/10 text-{{ $c }}-400 border border-{{ $c }}-500/20 px-2.5 py-0.5 rounded-full">
                                    {{ $labels[$bill->status] ?? $bill->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('bendahara.bills.show', $bill) }}"
                                    class="text-xs text-purple-400 hover:text-purple-300 transition-colors">Detail</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-white/5">
                {{ $bills->links() }}
            </div>
        @endif
    </div>

</x-simans-layout>