<x-simans-layout title="Detail Pengeluaran">

    <div class="mb-6">
        <a href="{{ route('bendahara.expenses.index') }}" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-3 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-white">{{ $expense->description }}</h1>
                <p class="text-gray-400 text-sm mt-0.5">
                    {{ $expense->fundSource->name ?? '-' }} · {{ $expense->category->name ?? '-' }} ·
                    {{ $expense->expense_date->format('d F Y') }}
                </p>
            </div>
            <span class="text-sm bg-{{ $expense->status_color }}-500/10 text-{{ $expense->status_color }}-400 border border-{{ $expense->status_color }}-500/20 px-3 py-1 rounded-full">
                {{ $expense->status_label }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 space-y-4">
            {{-- Detail --}}
            <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-white mb-4">Rincian</h2>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Jumlah</span>
                        <span class="text-white font-bold text-base">{{ $expense->amount_formatted }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Sumber dana</span>
                        <span class="text-white">{{ $expense->fundSource->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Kategori</span>
                        <span class="text-white">{{ $expense->category->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Tahun ajaran</span>
                        <span class="text-white">{{ $expense->academicYear->name ?? '-' }}</span>
                    </div>
                    @if($expense->period_label)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Periode</span>
                        <span class="text-white">{{ $expense->period_label }}</span>
                    </div>
                    @endif
                    @if($expense->reference_number)
                    <div class="flex justify-between">
                        <span class="text-gray-400">No. referensi</span>
                        <span class="text-white">{{ $expense->reference_number }}</span>
                    </div>
                    @endif
                    @if($expense->notes)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Catatan</span>
                        <span class="text-white text-right max-w-xs">{{ $expense->notes }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between border-t border-white/5 pt-2">
                        <span class="text-gray-400">Dicatat oleh</span>
                        <span class="text-white">{{ $expense->createdBy->name ?? '-' }}</span>
                    </div>
                    @if($expense->approvedBy)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Disetujui oleh</span>
                        <span class="text-white">{{ $expense->approvedBy->name }}</span>
                    </div>
                    @endif
                    @if($expense->rejection_reason)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Alasan ditolak</span>
                        <span class="text-red-400">{{ $expense->rejection_reason }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Log approval --}}
            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/5">
                    <h2 class="text-sm font-semibold text-white">Riwayat Approval</h2>
                </div>
                @if($expense->approvals->isEmpty())
                    <div class="px-5 py-6 text-center text-gray-500 text-sm">Belum ada riwayat.</div>
                @else
                    <div class="divide-y divide-white/5">
                        @foreach($expense->approvals as $log)
                        <div class="px-5 py-3.5 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-white">{{ $log->user->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                                @if($log->notes) <p class="text-xs text-gray-400 mt-0.5">{{ $log->notes }}</p> @endif
                            </div>
                            @php $ac = ['submitted'=>'gray','approved'=>'green','rejected'=>'red','revised'=>'amber'][$log->action] ?? 'gray'; @endphp
                            <span class="text-xs bg-{{ $ac }}-500/10 text-{{ $ac }}-400 border border-{{ $ac }}-500/20 px-2.5 py-0.5 rounded-full">
                                {{ $log->action_label }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Aksi Approval (Kepala Sekolah) --}}
        @if($expense->is_pending && in_array(auth()->user()->role, ['kepala_sekolah']))
        <div class="space-y-4">
            <div class="bg-gray-900 border border-amber-500/20 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-amber-400 mb-4">Tindakan Approval</h2>
                <div class="space-y-3">
                    <form method="POST" action="{{ route('bendahara.expenses.approve', $expense) }}">
                        @csrf @method('PATCH')
                        <div class="mb-3">
                            <label class="text-xs text-gray-400 mb-1 block">Catatan (opsional)</label>
                            <textarea name="notes" rows="2" placeholder="Catatan persetujuan..."
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-green-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <button type="submit" onclick="return confirm('Setujui pengeluaran Rp {{ number_format($expense->amount, 0, \'.\', \'.\') }}?')"
                            class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 rounded-lg transition-colors">
                            ✓ Setujui Pengeluaran
                        </button>
                    </form>

                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="w-full bg-red-600/10 hover:bg-red-600/20 border border-red-500/20 text-red-400 text-sm font-medium py-2.5 rounded-lg transition-colors">
                            ✕ Tolak
                        </button>
                        <div x-show="open" x-cloak class="mt-3">
                            <form method="POST" action="{{ route('bendahara.expenses.reject', $expense) }}">
                                @csrf @method('PATCH')
                                <textarea name="rejection_reason" required rows="2" placeholder="Alasan penolakan (wajib)..."
                                    class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-red-500 focus:outline-none resize-none mb-2"></textarea>
                                <button type="submit"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 rounded-lg transition-colors">
                                    Konfirmasi Penolakan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

</x-simans-layout>
