<x-simans-layout title="Detail Tagihan">

    <div class="mb-6">
        <a href="{{ route('siswa.payment.index') }}" class="text-gray-500 hover:text-blue-600 text-sm flex items-center gap-1 mb-3 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $bill->paymentType->name }}</h1>
                <p class="text-gray-500 text-sm mt-0.5">{{ $bill->period_label }} · {{ $bill->academicYear->name ?? '' }}</p>
            </div>
            @php
                $colors = ['unpaid'=>'red','partial'=>'amber','paid'=>'green','waived'=>'blue'];
                $labels = ['unpaid'=>'Belum bayar','partial'=>'Cicilan','paid'=>'Lunas','waived'=>'Dibebaskan'];
                $c = $colors[$bill->status] ?? 'gray';
            @endphp
            <span class="text-sm bg-{{ $c }}-50 text-{{ $c }}-700 border border-{{ $c }}-200 px-3 py-1 rounded-full">
                {{ $labels[$bill->status] ?? $bill->status }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Kiri: detail --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Rincian --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Rincian Tagihan</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tarif dasar</span>
                        <span class="text-gray-900">Rp {{ number_format($bill->amount_base, 0, ',', '.') }}</span>
                    </div>
                    @if($bill->amount_discount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Keringanan/beasiswa</span>
                        <span class="text-green-600">- Rp {{ number_format($bill->amount_discount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between border-t border-gray-200 pt-2 font-medium">
                        <span class="text-gray-600">Total tagihan</span>
                        <span class="text-gray-900">Rp {{ number_format($bill->amount_billed, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Sudah dibayar</span>
                        <span class="text-green-600">Rp {{ number_format($bill->amount_paid, 0, ',', '.') }}</span>
                    </div>
                    @if($bill->amount_remaining > 0 && $bill->status !== 'waived')
                    <div class="flex justify-between font-semibold">
                        <span class="text-red-600">Sisa</span>
                        <span class="text-red-600">Rp {{ number_format($bill->amount_remaining, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($bill->due_date)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jatuh tempo</span>
                        <span class="{{ \Carbon\Carbon::parse($bill->due_date)->isPast() && $bill->status !== 'paid' ? 'text-red-600' : 'text-gray-900' }}">
                            {{ \Carbon\Carbon::parse($bill->due_date)->format('d F Y') }}
                        </span>
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
                                    <p class="text-xs text-green-600">Bayar Rp {{ number_format($inst->amount_paid, 0, ',', '.') }}</p>
                                @endif
                            </div>
                            <span class="text-xs bg-{{ $ic }}-50 text-{{ $ic }}-700 border border-{{ $ic }}-200 px-2 py-0.5 rounded-full">
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
                                    <span class="text-xs text-gray-500">{{ $trx->channel === 'cash' ? 'Tunai' : 'Transfer' }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $trx->created_at->format('d/m/Y H:i') }}
                                    @if($trx->confirmedBy) · Dikonfirmasi {{ $trx->confirmedBy->name }} @endif
                                    @if($trx->rejection_reason)
                                        · <span class="text-red-600">{{ $trx->rejection_reason }}</span>
                                    @endif
                                </p>
                            </div>
                            <span class="text-xs bg-{{ $tc }}-50 text-{{ $tc }}-700 border border-{{ $tc }}-200 px-2.5 py-0.5 rounded-full">
                                {{ ['pending'=>'Menunggu konfirmasi','approved'=>'Diterima','rejected'=>'Ditolak'][$trx->status] ?? '-' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Kanan: Upload bukti --}}
        <div class="space-y-4">

            @if(!in_array($bill->status, ['paid','waived']))

            {{-- Info rekening sekolah --}}
            @if($school->bank_name || $school->bank_account)
            <div class="bg-white border border-amber-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-amber-600 mb-3">Rekening Pembayaran</h2>
                <div class="space-y-2 text-sm">
                    @if($school->bank_name)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Bank</span>
                        <span class="text-gray-900 font-medium">{{ $school->bank_name }}</span>
                    </div>
                    @endif
                    @if($school->bank_account)
                    <div class="flex justify-between">
                        <span class="text-gray-500">No. Rekening</span>
                        <span class="text-gray-900 font-bold text-base">{{ $school->bank_account }}</span>
                    </div>
                    @endif
                    @if($school->bank_account_name)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Atas Nama</span>
                        <span class="text-gray-900">{{ $school->bank_account_name }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Form upload --}}
            @if($bill->transactions->where('status','pending')->isEmpty())
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Upload Bukti Transfer</h2>
                <form method="POST" action="{{ route('siswa.payment.upload', $bill) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-3">
                        @if($bill->installments->isNotEmpty())
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Untuk cicilan</label>
                            <select name="installment_id"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                                <option value="">Tanpa cicilan</option>
                                @foreach($bill->installments->where('status','!=','paid') as $inst)
                                    <option value="{{ $inst->id }}">
                                        Cicilan ke-{{ $inst->installment_number }}
                                        (Rp {{ number_format($inst->amount_due - $inst->amount_paid, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Jumlah Transfer (Rp) *</label>
                            <input type="number" name="amount" required min="1"
                                placeholder="{{ $bill->amount_remaining }}"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            <p class="text-xs text-gray-500 mt-1">Sisa: Rp {{ number_format($bill->amount_remaining, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Nama Bank Pengirim *</label>
                            <input type="text" name="bank_name" required placeholder="BCA, BRI, Mandiri, dll"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Nama Pengirim *</label>
                            <input type="text" name="sender_name" required placeholder="Nama sesuai rekening"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Tanggal Transfer *</label>
                            <input type="date" name="transfer_date" required
                                max="{{ date('Y-m-d') }}"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Bukti Transfer * (JPG/PNG/PDF, maks 2MB)</label>
                            <input type="file" name="receipt" required accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full bg-white border border-gray-200 text-white text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none file:mr-3 file:text-xs file:bg-blue-600 file:text-white file:border-0 file:rounded file:px-2 file:py-1">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Catatan</label>
                            <textarea name="notes" rows="2" placeholder="Opsional..."
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2.5 rounded-lg transition-colors">
                            Kirim Bukti Transfer
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-center">
                <svg class="w-8 h-8 text-amber-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-amber-600 font-medium">Menunggu konfirmasi</p>
                <p class="text-xs text-gray-500 mt-1">Bukti transfer Anda sedang diperiksa oleh bendahara</p>
            </div>
            @endif

            @else
            <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center">
                <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-green-600 font-medium">
                    {{ $bill->status === 'paid' ? 'Tagihan Lunas' : 'Tagihan Dibebaskan' }}
                </p>
            </div>
            @endif

        </div>
    </div>

</x-simans-layout>
