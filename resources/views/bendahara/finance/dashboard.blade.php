<x-simans-layout title="Keuangan Sekolah">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Keuangan Sekolah</h1>
            <p class="text-gray-500 text-sm mt-0.5">Ringkasan saldo & transaksi keuangan</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('bendahara.expenses.create') }}"
                class="flex items-center gap-2 bg-red-50 hover:bg-red-50 border border-red-200 text-red-600 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Catat Pengeluaran
            </a>
        </div>
    </div>

    {{-- Ringkasan total --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <p class="text-xs text-gray-600 mb-1 uppercase tracking-wide">Total Pemasukan</p>
            <p class="text-xl font-bold text-green-600">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <p class="text-xs text-gray-600 mb-1 uppercase tracking-wide">Total Pengeluaran</p>
            <p class="text-xl font-bold text-red-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-{{ $totalBalance >= 0 ? 'green' : 'red' }}-500/20 rounded-xl p-5">
            <p class="text-xs text-gray-600 mb-1 uppercase tracking-wide">Saldo</p>
            <p class="text-xl font-bold {{ $totalBalance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                Rp {{ number_format(abs($totalBalance), 0, ',', '.') }}
                {{ $totalBalance < 0 ? '(Defisit)' : '' }}
            </p>
        </div>
    </div>

    {{-- Saldo per sumber dana --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
        <div class="tbl-card">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Saldo per Sumber Dana</h2>
                <a href="{{ route('bendahara.fund-sources.index') }}" class="text-xs text-blue-600 hover:text-blue-600">Kelola</a>
            </div>
            @if($sources->isEmpty())
                <div class="px-5 py-8 text-center">
                    <p class="text-gray-600 text-sm">Belum ada sumber dana.</p>
                    <a href="{{ route('bendahara.fund-sources.index') }}" class="text-xs text-blue-600 mt-2 inline-block">+ Tambah Sumber Dana</a>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($sources as $source)
                    <div class="px-5 py-3.5 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900">{{ $source->name }}</span>
                                <span class="text-xs bg-{{ $source->getTypeBadgeColor() }}-500/10 text-{{ $source->getTypeBadgeColor() }}-400 border border-{{ $source->getTypeBadgeColor() }}-500/20 px-2 py-0.5 rounded-full">
                                    {{ $source->getTypeLabel() }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 mt-0.5">
                                Masuk: Rp {{ number_format($source->income_total, 0, ',', '.') }} ·
                                Keluar: Rp {{ number_format($source->expense_total, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold {{ $source->balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                                Rp {{ number_format(abs($source->balance), 0, ',', '.') }}
                            </p>
                            <p class="text-xs {{ $source->balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $source->balance >= 0 ? 'Saldo' : 'Defisit' }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Menunggu Approval --}}
        <div class="tbl-card">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Menunggu Approval</h2>
                @if($pendingExpenses->isNotEmpty())
                    <a href="{{ route('bendahara.expenses.pending') }}" class="text-xs text-amber-600 hover:text-amber-700">
                        Lihat semua →
                    </a>
                @endif
            </div>
            @if($pendingExpenses->isEmpty())
                <div class="px-5 py-8 text-center">
                    <p class="text-gray-600 text-sm">Tidak ada pengeluaran yang menunggu approval.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($pendingExpenses as $exp)
                    <div class="px-5 py-3.5 flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 truncate">{{ $exp->description }}</p>
                            <p class="text-xs text-gray-600">
                                {{ $exp->category->name ?? '-' }} · {{ $exp->fundSource->name ?? '-' }}
                                · {{ $exp->createdBy->name ?? '-' }}
                            </p>
                        </div>
                        <div class="ml-4 text-right shrink-0">
                            <p class="text-sm font-semibold text-amber-600">{{ $exp->amount_formatted }}</p>
                            <a href="{{ route('bendahara.expenses.show', $exp) }}" class="text-xs text-blue-600 hover:text-blue-600">Detail</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Pengeluaran terbaru --}}
    <div class="tbl-card">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Pengeluaran Terbaru</h2>
            <a href="{{ route('bendahara.expenses.index') }}" class="text-xs text-blue-600 hover:text-blue-600">Lihat semua →</a>
        </div>
        @if($recentExpenses->isEmpty())
            <div class="px-5 py-8 text-center">
                <p class="text-gray-600 text-sm">Belum ada pengeluaran.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($recentExpenses as $exp)
                <div class="px-5 py-3.5 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-900">{{ $exp->description }}</p>
                        <p class="text-xs text-gray-600">
                            {{ $exp->expense_date->format('d/m/Y') }} ·
                            {{ $exp->category->name ?? '-' }} ·
                            {{ $exp->fundSource->name ?? '-' }}
                        </p>
                    </div>
                    <p class="text-sm font-semibold text-red-600">{{ $exp->amount_formatted }}</p>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Menu cepat --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
        <a href="{{ route('bendahara.fund-sources.index') }}" class="flex items-center gap-3 bg-white hover:bg-white border border-gray-200 rounded-xl p-4 transition-colors">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75"/></svg>
            <span class="text-sm font-medium text-gray-900">Sumber Dana</span>
        </a>
        <a href="{{ route('bendahara.expenses.index') }}" class="flex items-center gap-3 bg-white hover:bg-white border border-gray-200 rounded-xl p-4 transition-colors">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"/></svg>
            <span class="text-sm font-medium text-gray-900">Pengeluaran</span>
        </a>
        <a href="{{ route('bendahara.expenses.categories') }}" class="flex items-center gap-3 bg-white hover:bg-white border border-gray-200 rounded-xl p-4 transition-colors">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>
            <span class="text-sm font-medium text-gray-900">Kategori</span>
        </a>
        <a href="{{ route('bendahara.payroll.index') }}" class="flex items-center gap-3 bg-white hover:bg-white border border-gray-200 rounded-xl p-4 transition-colors">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
            <span class="text-sm font-medium text-gray-900">Penggajian</span>
        </a>
    </div>

</x-simans-layout>
