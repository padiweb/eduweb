<x-simans-layout title="Pengeluaran">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Pengeluaran</h1>
            <p class="text-gray-500 text-sm mt-0.5">Semua transaksi pengeluaran sekolah</p>
        </div>
        <div class="flex gap-2">
            @if($totalPending > 0)
                <a href="{{ route('bendahara.expenses.pending') }}"
                    class="flex items-center gap-2 bg-amber-50 hover:bg-amber-50 border border-amber-200 text-amber-600 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    {{ $totalPending }} Menunggu Approval
                </a>
            @endif
            <a href="{{ route('bendahara.expenses.create') }}"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Catat Pengeluaran
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
            <p class="text-xs text-gray-500 mb-1">Total Disetujui</p>
            <p class="text-lg font-bold text-red-600">Rp {{ number_format($totalApproved, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-amber-200 rounded-xl px-4 py-3">
            <p class="text-xs text-gray-500 mb-1">Menunggu Approval</p>
            <p class="text-lg font-bold text-amber-600">{{ $totalPending }} pengeluaran</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari keterangan..."
            class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 w-44 focus:border-blue-500 focus:outline-none">
        <select name="status" class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
            <option value="">Semua status</option>
            <option value="draft"            {{ request('status')=='draft'            ? 'selected':'' }}>Draft</option>
            <option value="pending_approval" {{ request('status')=='pending_approval' ? 'selected':'' }}>Menunggu Approval</option>
            <option value="approved"         {{ request('status')=='approved'         ? 'selected':'' }}>Disetujui</option>
            <option value="rejected"         {{ request('status')=='rejected'         ? 'selected':'' }}>Ditolak</option>
        </select>
        <select name="source" class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
            <option value="">Semua sumber</option>
            @foreach($sources as $s)
                <option value="{{ $s->id }}" {{ request('source')==$s->id ? 'selected':'' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <select name="category" class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
            <option value="">Semua kategori</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ request('category')==$c->id ? 'selected':'' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['search','status','source','category','year']))
            <a href="{{ route('bendahara.expenses.index') }}" class="text-gray-500 hover:text-blue-600 text-sm px-3 py-2">Reset</a>
        @endif
    </form>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($expenses->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-500">Belum ada pengeluaran yang sesuai filter.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Tanggal</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Keterangan</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Sumber / Kategori</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Jumlah</th>
                            <th class="text-center text-xs text-gray-500 font-medium px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($expenses as $exp)
                        <tr class="hover:bg-white/2 transition-colors">
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $exp->expense_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <p class="text-gray-900">{{ $exp->description }}</p>
                                @if($exp->reference_number)
                                    <p class="text-xs text-gray-500">{{ $exp->reference_number }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-gray-600 text-xs">{{ $exp->fundSource->name ?? '-' }}</p>
                                <p class="text-gray-500 text-xs">{{ $exp->category->name ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-red-600 whitespace-nowrap">{{ $exp->amount_formatted }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs bg-{{ $exp->status_color }}-500/10 text-{{ $exp->status_color }}-400 border border-{{ $exp->status_color }}-500/20 px-2.5 py-0.5 rounded-full">
                                    {{ $exp->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('bendahara.expenses.show', $exp) }}" class="text-xs text-blue-600 hover:text-blue-500">Detail</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">{{ $expenses->links() }}</div>
        @endif
    </div>

</x-simans-layout>
