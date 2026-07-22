<x-simans-layout title="Status Pembayaran">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Status Pembayaran</h1>
        <p class="text-gray-500 text-sm mt-0.5">Lihat tagihan dan riwayat pembayaran Anda</p>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Total Tagihan</p>
            <p class="text-lg font-bold text-gray-900">Rp {{ number_format($summary['total_tagihan'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Sudah Dibayar</p>
            <p class="text-lg font-bold text-green-400">Rp {{ number_format($summary['total_bayar'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Tunggakan</p>
            <p class="text-lg font-bold text-red-400">Rp {{ number_format($summary['tunggakan'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Tagihan Lunas</p>
            <p class="text-lg font-bold text-purple-400">{{ $summary['lunas'] }} tagihan</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-4">
        <select name="status" class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
            <option value="">Semua status</option>
            <option value="unpaid"  {{ request('status') == 'unpaid'  ? 'selected' : '' }}>Belum bayar</option>
            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Cicilan</option>
            <option value="paid"    {{ request('status') == 'paid'    ? 'selected' : '' }}>Lunas</option>
        </select>
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-gray-900 text-sm px-4 py-2 rounded-lg">Filter</button>
        @if(request('status'))
            <a href="{{ route('siswa.payment.index') }}" class="text-gray-500 hover:text-gray-900 text-sm px-3 py-2 rounded-lg">Reset</a>
        @endif
    </form>

    {{-- Daftar tagihan --}}
    <div class="space-y-3">
        @forelse($bills as $bill)
        @php
            $colors = ['unpaid'=>'red','partial'=>'amber','paid'=>'green','waived'=>'blue'];
            $labels = ['unpaid'=>'Belum bayar','partial'=>'Cicilan','paid'=>'Lunas','waived'=>'Dibebaskan'];
            $c = $colors[$bill->status] ?? 'gray';
        @endphp
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <p class="text-sm font-medium text-gray-900">{{ $bill->paymentType->name }}</p>
                    <span class="text-xs bg-{{ $c }}-500/10 text-{{ $c }}-400 border border-{{ $c }}-500/20 px-2 py-0.5 rounded-full">
                        {{ $labels[$bill->status] ?? $bill->status }}
                    </span>
                    {{-- Badge pending --}}
                    @if($bill->transactions->where('status','pending')->isNotEmpty())
                        <span class="text-xs bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-0.5 rounded-full">
                            Menunggu konfirmasi
                        </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500">{{ $bill->period_label }}</p>
                @if($bill->due_date && $bill->status !== 'paid')
                    <p class="text-xs {{ \Carbon\Carbon::parse($bill->due_date)->isPast() ? 'text-red-400' : 'text-gray-400' }} mt-0.5">
                        Jatuh tempo {{ \Carbon\Carbon::parse($bill->due_date)->format('d/m/Y') }}
                    </p>
                @endif
            </div>
            <div class="text-right shrink-0">
                <p class="text-sm font-semibold text-gray-900">Rp {{ number_format($bill->amount_billed, 0, ',', '.') }}</p>
                @if($bill->amount_remaining > 0 && $bill->status !== 'waived')
                    <p class="text-xs text-red-400">Sisa Rp {{ number_format($bill->amount_remaining, 0, ',', '.') }}</p>
                @endif
                @if($bill->amount_discount > 0)
                    <p class="text-xs text-green-400">Diskon Rp {{ number_format($bill->amount_discount, 0, ',', '.') }}</p>
                @endif
            </div>
            <a href="{{ route('siswa.payment.show', $bill) }}"
                class="shrink-0 text-xs bg-purple-600 hover:bg-purple-700 text-gray-900 px-3 py-1.5 rounded-lg transition-colors">
                Detail
            </a>
        </div>
        @empty
            <div class="bg-white border border-gray-200 rounded-xl px-5 py-12 text-center">
                <p class="text-gray-400">Belum ada tagihan.</p>
            </div>
        @endforelse
    </div>

    @if($bills->hasPages())
        <div class="mt-4">{{ $bills->links() }}</div>
    @endif

</x-simans-layout>
