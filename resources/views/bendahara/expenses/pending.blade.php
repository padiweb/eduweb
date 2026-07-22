<x-simans-layout title="Menunggu Approval">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Pengeluaran Menunggu Approval</h1>
            <p class="text-gray-500 text-sm mt-0.5">Persetujuan kepala sekolah diperlukan</p>
        </div>
        <a href="{{ route('bendahara.expenses.index') }}" class="text-gray-500 hover:text-gray-900 text-sm">← Semua pengeluaran</a>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($expenses->isEmpty())
            <div class="px-5 py-12 text-center"><p class="text-gray-500">Tidak ada pengeluaran yang menunggu approval.</p></div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($expenses as $exp)
                <div class="px-5 py-4 flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $exp->description }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $exp->fundSource->name ?? '-' }} · {{ $exp->category->name ?? '-' }} ·
                            {{ $exp->expense_date->format('d/m/Y') }} · {{ $exp->createdBy->name ?? '-' }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-base font-bold text-amber-400">{{ $exp->amount_formatted }}</p>
                    </div>
                    <a href="{{ route('bendahara.expenses.show', $exp) }}"
                        class="shrink-0 text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg transition-colors">
                        Review
                    </a>
                </div>
                @endforeach
            </div>
            <div class="px-4 py-3 border-t border-gray-200">{{ $expenses->links() }}</div>
        @endif
    </div>
</x-simans-layout>
