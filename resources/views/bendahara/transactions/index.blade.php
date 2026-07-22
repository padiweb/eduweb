<x-simans-layout title="Konfirmasi Transfer">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Konfirmasi Transfer</h1>
            <p class="text-gray-500 text-sm mt-0.5">Verifikasi bukti transfer dari siswa/orang tua</p>
        </div>
        <div class="flex gap-2">
            @foreach(['pending'=>'Menunggu','approved'=>'Diterima','rejected'=>'Ditolak'] as $s => $l)
                <a href="{{ route('bendahara.transactions.index', ['status' => $s]) }}"
                    class="text-xs px-3 py-1.5 rounded-lg border transition-colors {{ $status === $s ? 'bg-blue-600 border-purple-600 text-white' : 'bg-white border-gray-200 text-gray-500 hover:text-white' }}">
                    {{ $l }}
                </a>
            @endforeach
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($transactions->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-500">Tidak ada transaksi {{ $status === 'pending' ? 'yang menunggu konfirmasi' : $status }}.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($transactions as $trx)
                <div class="px-5 py-4 flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <p class="text-sm font-medium text-gray-900">{{ $trx->bill->student->name ?? '-' }}</p>
                            <span class="text-xs text-gray-500">{{ $trx->bill->student->nis ?? '' }}</span>
                        </div>
                        <p class="text-xs text-gray-500">{{ $trx->bill->paymentType->name ?? '-' }} · {{ $trx->bill->period_label ?? '-' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $trx->bank_name ?? '-' }} · a.n. {{ $trx->sender_name ?? '-' }}
                            · {{ $trx->transfer_date ? \Carbon\Carbon::parse($trx->transfer_date)->format('d/m/Y') : '-' }}
                            · {{ $trx->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-base font-semibold text-gray-900">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">{{ $trx->reference_number }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($trx->receipt_path)
                            <a href="{{ route('bendahara.transactions.receipt', $trx) }}" target="_blank"
                                class="text-xs bg-white hover:bg-gray-50 border border-gray-200 text-gray-400 px-3 py-1.5 rounded-lg transition-colors">
                                Bukti
                            </a>
                        @endif
                        @if($trx->status === 'pending')
                            <form method="POST" action="{{ route('bendahara.transactions.approve', $trx) }}">
                                @csrf @method('PATCH')
                                <button type="submit" onclick="return confirm('Konfirmasi pembayaran ini?')"
                                    class="text-xs bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg transition-colors">
                                    Terima
                                </button>
                            </form>
                            <button onclick="openReject({{ $trx->id }})"
                                class="text-xs bg-red-600/20 hover:bg-red-600/40 border border-red-500/30 text-red-400 px-3 py-1.5 rounded-lg transition-colors">
                                Tolak
                            </button>
                        @else
                            @php $tc = ['approved'=>'green','rejected'=>'red'][$trx->status] ?? 'gray'; @endphp
                            <span class="text-xs bg-{{ $tc }}-500/10 text-{{ $tc }}-400 border border-{{ $tc }}-500/20 px-2.5 py-1 rounded-lg">
                                {{ ['approved'=>'Diterima','rejected'=>'Ditolak'][$trx->status] ?? '-' }}
                            </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    {{-- Modal tolak --}}
    <div id="modal-reject" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
        <div class="bg-white border border-gray-200 rounded-xl w-full max-w-sm p-6">
            <h3 class="text-gray-900 font-semibold mb-4">Tolak Transfer</h3>
            <form id="form-reject" method="POST" action="">
                @csrf @method('PATCH')
                <div class="mb-4">
                    <label class="text-xs text-gray-500 mb-1 block">Alasan penolakan *</label>
                    <textarea name="rejection_reason" required rows="3"
                        placeholder="Contoh: Bukti tidak jelas, nominal tidak sesuai..."
                        class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-red-500 focus:outline-none resize-none"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modal-reject').classList.add('hidden')"
                        class="flex-1 bg-white hover:bg-gray-50 text-gray-400 text-sm font-medium py-2 rounded-lg transition-colors">Batal</button>
                    <button type="submit"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 rounded-lg transition-colors">Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openReject(id) {
        document.getElementById('form-reject').action = `/bendahara/transactions/${id}/reject`;
        document.getElementById('modal-reject').classList.remove('hidden');
    }
    document.getElementById('modal-reject').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
    </script>

</x-simans-layout>