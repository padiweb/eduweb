<x-simans-layout title="Detail Tagihan">

    <div class="mb-6">
        <a href="{{ route('bendahara.bills.index') }}" class="text-gray-500 hover:text-gray-900 text-sm flex items-center gap-1 mb-3 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali ke Daftar
        </a>
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold text-gray-900">{{ $bill->paymentType->name }} — {{ $bill->period_label }}</h1>
                    <a href="{{ route('bendahara.bills.edit', $bill) }}"
                        class="text-xs bg-white hover:bg-gray-50 border border-gray-200 text-gray-400 px-3 py-1 rounded-lg transition-colors">
                        Edit
                    </a>
                </div>
                <p class="text-gray-500 text-sm mt-0.5">{{ $bill->student->name }} · {{ $bill->student->nis ?? $bill->student->username }}</p>
            </div>
            @php
                $colors = ['unpaid'=>'red','partial'=>'amber','paid'=>'green','waived'=>'blue'];
                $labels = ['unpaid'=>'Belum bayar','partial'=>'Cicilan','paid'=>'Lunas','waived'=>'Dibebaskan'];
                $c = $colors[$bill->status] ?? 'gray';
            @endphp
            <span class="text-sm bg-{{ $c }}-500/10 text-{{ $c }}-400 border border-{{ $c }}-500/20 px-3 py-1 rounded-full">
                {{ $labels[$bill->status] ?? $bill->status }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-lg px-4 py-3 mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Kiri: detail --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Rincian nominal --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Rincian Tagihan</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tarif dasar</span>
                        <span class="text-gray-900">Rp {{ number_format($bill->amount_base, 0, ',', '.') }}</span>
                    </div>
                    @if($bill->amount_discount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Diskon/beasiswa</span>
                        <span class="text-green-400">- Rp {{ number_format($bill->amount_discount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between border-t border-gray-200 pt-2 font-medium">
                        <span class="text-gray-400">Total tagihan</span>
                        <span class="text-gray-900">Rp {{ number_format($bill->amount_billed, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Sudah dibayar</span>
                        <span class="text-green-400">Rp {{ number_format($bill->amount_paid, 0, ',', '.') }}</span>
                    </div>
                    @if($bill->amount_remaining > 0 && $bill->status !== 'waived')
                    <div class="flex justify-between font-semibold">
                        <span class="text-red-400">Sisa</span>
                        <span class="text-red-400">Rp {{ number_format($bill->amount_remaining, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Cicilan --}}
            @if($bill->installments->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Jadwal Cicilan</h2>
                <div class="space-y-2">
                    @foreach($bill->installments as $inst)
                    @php $ic = ['unpaid'=>'red','partial'=>'amber','paid'=>'green'][$inst->status] ?? 'gray'; @endphp
                    <div class="flex items-center justify-between bg-white rounded-lg px-4 py-2.5">
                        <div>
                            <span class="text-sm text-gray-900">Cicilan ke-{{ $inst->installment_number }}</span>
                            <span class="text-xs text-gray-500 ml-2">{{ \Carbon\Carbon::parse($inst->due_date)->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-sm text-gray-900">Rp {{ number_format($inst->amount_due, 0, ',', '.') }}</p>
                                @if($inst->amount_paid > 0)
                                    <p class="text-xs text-green-400">Bayar Rp {{ number_format($inst->amount_paid, 0, ',', '.') }}</p>
                                @endif
                            </div>
                            <span class="text-xs bg-{{ $ic }}-500/10 text-{{ $ic }}-400 border border-{{ $ic }}-500/20 px-2 py-0.5 rounded-full">
                                {{ ['unpaid'=>'Belum','partial'=>'Sebagian','paid'=>'Lunas'][$inst->status] ?? '-' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Riwayat transaksi --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-900">Riwayat Pembayaran</h2>
                </div>
                @if($bill->transactions->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <p class="text-gray-500 text-sm">Belum ada transaksi</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($bill->transactions as $trx)
                        @php $tc = ['pending'=>'amber','approved'=>'green','rejected'=>'red'][$trx->status] ?? 'gray'; @endphp
                        <div class="px-5 py-3.5 flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900">Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
                                    @php
                                        $cl = match($trx->channel) {
                                            'cash'=>'Tunai','transfer'=>'Transfer',
                                            'scholarship_cash'=>'Beasiswa Dana',
                                            'scholarship_waiver'=>'Beasiswa Potong',
                                            default=>'Beasiswa'
                                        };
                                    @endphp
                                    <span class="text-xs text-gray-500">{{ $cl }}</span>
                                    @if($trx->channel === 'transfer' && $trx->receipt_path)
                                        <a href="{{ route('bendahara.transactions.receipt', $trx) }}" target="_blank"
                                            class="text-xs text-blue-400 hover:text-blue-300">Lihat bukti</a>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $trx->created_at->format('d/m/Y H:i') }}
                                    @if($trx->confirmedBy) · {{ $trx->confirmedBy->name }} @endif
                                    @if($trx->rejection_reason) · <span class="text-red-400">{{ $trx->rejection_reason }}</span> @endif
                                </p>
                            </div>
                            <span class="text-xs bg-{{ $tc }}-500/10 text-{{ $tc }}-400 border border-{{ $tc }}-500/20 px-2.5 py-0.5 rounded-full">
                                {{ ['pending'=>'Menunggu','approved'=>'Diterima','rejected'=>'Ditolak'][$trx->status] ?? '-' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Kanan: Aksi --}}
        <div class="space-y-4">

            @if(!in_array($bill->status, ['paid','waived']))
            {{-- Input bayar tunai --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Input Bayar Tunai</h2>
                <form method="POST" action="{{ route('bendahara.bills.cash', $bill) }}">
                    @csrf
                    {{-- pay_type wajib di storeCash --}}
                    <input type="hidden" name="pay_type" id="show-pay-type" value="partial">
                    <div class="space-y-3">
                        {{-- Pilihan: Lunas atau Cicilan --}}
                        <div class="flex gap-2">
                            <button type="button" id="btn-partial"
                                onclick="setPayType('partial')"
                                class="flex-1 text-xs font-medium py-2 rounded-lg border bg-gray-100 border-white/30 text-gray-900 transition-colors">
                                Cicilan
                            </button>
                            <button type="button" id="btn-full"
                                onclick="setPayType('full')"
                                class="flex-1 text-xs font-medium py-2 rounded-lg border bg-white border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">
                                Lunas (Rp {{ number_format($bill->amount_remaining, 0, ',', '.') }})
                            </button>
                        </div>
                        {{-- Input nominal — hanya tampil saat cicilan --}}
                        <div id="show-amount-wrap">
                            <label class="text-xs text-gray-500 mb-1 block">Jumlah (Rp) *</label>
                            <input type="number" name="amount" id="show-amount" min="1"
                                max="{{ $bill->amount_remaining }}"
                                placeholder="{{ $bill->amount_remaining }}"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            <p class="text-xs text-gray-500 mt-1">Sisa: Rp {{ number_format($bill->amount_remaining, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Catatan</label>
                            <input type="text" name="cashier_notes" placeholder="Opsional..."
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 rounded-lg transition-colors">
                            Catat Pembayaran
                        </button>
                    </div>
                </form>
                <script>
                function setPayType(type) {
                    document.getElementById('show-pay-type').value = type;
                    var amountWrap  = document.getElementById('show-amount-wrap');
                    var amountInput = document.getElementById('show-amount');
                    var btnPartial  = document.getElementById('btn-partial');
                    var btnFull     = document.getElementById('btn-full');
                    if (type === 'full') {
                        amountWrap.style.display = 'none';
                        amountInput.removeAttribute('required');
                        btnFull.className    = btnFull.className.replace('bg-white border-gray-200 text-gray-500','').trim() + ' bg-gray-100 border-white/30 text-gray-900';
                        btnPartial.className = btnPartial.className.replace('bg-gray-100 border-white/30 text-gray-900','').trim() + ' bg-white border-gray-200 text-gray-500';
                    } else {
                        amountWrap.style.display = 'block';
                        amountInput.setAttribute('required','required');
                        btnPartial.className = btnPartial.className.replace('bg-white border-gray-200 text-gray-500','').trim() + ' bg-gray-100 border-white/30 text-gray-900';
                        btnFull.className    = btnFull.className.replace('bg-gray-100 border-white/30 text-gray-900','').trim() + ' bg-white border-gray-200 text-gray-500';
                    }
                }
                </script>
            </div>

            {{-- Bebaskan tagihan --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5" x-data="{ open: false }">
                <button @click="open = !open" class="text-sm text-red-400 hover:text-red-300 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    Bebaskan Tagihan
                </button>
                <div x-show="open" x-cloak class="mt-3">
                    <form method="POST" action="{{ route('bendahara.bills.waive', $bill) }}">
                        @csrf @method('PATCH')
                        <input type="text" name="reason" required placeholder="Alasan pembebasan (wajib)..."
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-red-500 focus:outline-none mb-2">
                        <button type="submit" onclick="return confirm('Yakin bebaskan tagihan ini?')"
                            class="w-full bg-red-600/20 hover:bg-red-600/40 border border-red-500/30 text-red-400 text-sm font-medium py-2 rounded-lg transition-colors">
                            Konfirmasi Pembebasan
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Info --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 text-sm space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Jatuh tempo</span>
                    <span class="text-gray-900">{{ $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tahun ajaran</span>
                    <span class="text-gray-900">{{ $bill->academicYear->name ?? '-' }} Sem {{ $bill->academicYear->semester ?? '' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Dibuat</span>
                    <span class="text-gray-900">{{ $bill->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

</x-simans-layout>
