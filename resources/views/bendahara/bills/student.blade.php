<x-simans-layout title="Tagihan — {{ $student->name }}">

    <div class="mb-6">
        <a href="{{ route('bendahara.bills.index') }}" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-3 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>

        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-purple-900/50 border border-purple-500/20 flex items-center justify-center text-lg font-bold text-purple-300 flex-shrink-0">
                    {{ strtoupper(substr($student->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">{{ $student->name }}</h1>
                    <p class="text-gray-400 text-sm mt-0.5">{{ $student->nis ?? '-' }}</p>
                </div>
            </div>
            <a href="{{ route('bendahara.bills.create') }}"
                class="text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 border border-white/10 px-3 py-2 rounded-lg transition-colors">
                + Tagihan Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-lg px-4 py-3 mb-4">{{ $errors->first() }}</div>
    @endif

    {{-- Ringkasan --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-900 border border-white/5 rounded-xl px-4 py-3 text-center">
            <p class="text-xs text-gray-500 mb-1">Total Tagihan</p>
            <p class="text-base font-bold text-white">Rp {{ number_format($totalBilled, 0, ',', '.') }}</p>
        </div>
        <div class="bg-gray-900 border border-white/5 rounded-xl px-4 py-3 text-center">
            <p class="text-xs text-gray-500 mb-1">Sudah Dibayar</p>
            <p class="text-base font-bold text-green-400">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
        </div>
        <div class="bg-gray-900 border {{ $totalRemaining > 0 ? 'border-red-500/20' : 'border-white/5' }} rounded-xl px-4 py-3 text-center">
            <p class="text-xs text-gray-500 mb-1">Sisa Tagihan</p>
            <p class="text-base font-bold {{ $totalRemaining > 0 ? 'text-red-400' : 'text-gray-400' }}">
                {{ $totalRemaining > 0 ? 'Rp ' . number_format($totalRemaining, 0, ',', '.') : 'Lunas' }}
            </p>
        </div>
    </div>

    {{-- Filter tahun --}}
    <form method="GET" class="flex gap-3 mb-5">
        <select name="year" onchange="this.form.submit()"
            class="bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
            @foreach($academicYears as $y)
                <option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>
                    {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' ✓' : '' }}
                </option>
            @endforeach
        </select>
    </form>

    {{-- Daftar tagihan siswa --}}
    @if($bills->isEmpty())
        <div class="bg-gray-900 border border-white/5 rounded-xl px-5 py-12 text-center">
            <p class="text-gray-500">Belum ada tagihan untuk tahun ajaran ini.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($bills as $bill)
            @php
                $remaining = $bill->amount_remaining;
                $colors = ['unpaid'=>'red','partial'=>'amber','paid'=>'green','waived'=>'blue'];
                $labels = ['unpaid'=>'Belum bayar','partial'=>'Cicilan','paid'=>'Lunas','waived'=>'Dibebaskan'];
                $c = $colors[$bill->status] ?? 'gray';
            @endphp

            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                {{-- Header tagihan --}}
                <div class="px-5 py-4 flex items-center justify-between border-b border-white/5">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-white">{{ $bill->paymentType->name }}</p>
                            <span class="text-xs bg-{{ $c }}-500/10 text-{{ $c }}-400 border border-{{ $c }}-500/20 px-2 py-0.5 rounded-full">
                                {{ $labels[$bill->status] }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $bill->period_label }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-white">Rp {{ number_format($bill->amount_billed, 0, ',', '.') }}</p>
                        @if($remaining > 0 && $bill->status !== 'waived')
                            <p class="text-xs text-red-400">Sisa Rp {{ number_format($remaining, 0, ',', '.') }}</p>
                        @elseif($bill->amount_paid > 0)
                            <p class="text-xs text-green-400">Dibayar Rp {{ number_format($bill->amount_paid, 0, ',', '.') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Riwayat transaksi --}}
                @if($bill->transactions->where('status','approved')->isNotEmpty())
                <div class="px-5 py-2 border-b border-white/5">
                    @foreach($bill->transactions->where('status','approved') as $trx)
                    <div class="flex items-center justify-between py-1.5 text-xs">
                        <span class="text-gray-500">
                            {{ $trx->created_at->format('d/m/Y H:i') }} ·
                            {{ $trx->channel === 'scholarship' ? 'Beasiswa' : ($trx->channel === 'cash' ? 'Tunai' : 'Transfer') }}
                            @if($trx->cashier_notes) · {{ $trx->cashier_notes }} @endif
                        </span>
                        <span class="text-green-400 font-medium">+ Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Aksi --}}
                @if(!in_array($bill->status, ['paid','waived']))
                <div class="px-5 py-3 flex items-center gap-2">
                    {{-- Tombol Bayar --}}
                    <button
                        onclick="openBayar({{ $bill->id }}, {{ $remaining }}, '{{ addslashes($bill->paymentType->name) }} - {{ addslashes($bill->period_label) }}')"
                        class="text-xs bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg transition-colors">
                        💰 Bayar
                    </button>
                    <a href="{{ route('bendahara.bills.edit', $bill) }}"
                        class="text-xs text-gray-500 hover:text-white px-3 py-1.5 rounded-lg border border-white/5 hover:border-white/20 transition-colors">
                        Edit
                    </a>
                    <form method="POST" action="{{ route('bendahara.bills.destroy', $bill) }}"
                        onsubmit="return confirm('Hapus tagihan ini?')" class="ml-auto">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-gray-600 hover:text-red-400 transition-colors">Hapus</button>
                    </form>
                </div>
                @else
                <div class="px-5 py-2.5 flex items-center justify-between">
                    <span class="text-xs text-{{ $c }}-400">{{ $labels[$bill->status] }}</span>
                    <a href="{{ route('bendahara.bills.edit', $bill) }}" class="text-xs text-gray-600 hover:text-white">Edit</a>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    @endif

    {{-- MODAL: Bayar --}}
    <div id="modal-bayar" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
        x-data="{
            payType: 'full',
            billId: 0,
            remaining: 0,
            label: '',
            discountId: ''
        }" x-init="
            window.openBayar = (id, rem, lbl) => {
                $data.billId    = id;
                $data.remaining = rem;
                $data.label     = lbl;
                $data.payType   = 'full';
                $data.discountId = '';
                document.getElementById('modal-bayar').classList.remove('hidden');
            }
        ">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-white font-semibold">Catat Pembayaran</h3>
                <button onclick="document.getElementById('modal-bayar').classList.add('hidden')"
                    class="text-gray-500 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <p class="text-sm text-gray-400 mb-1" x-text="label"></p>
            <p class="text-lg font-bold text-white mb-4">
                Sisa: Rp <span x-text="remaining.toLocaleString('id-ID')"></span>
            </p>

            {{-- Form bayar --}}
            <template x-for="bill in [billId]" :key="bill">
                <div>
                    <form :action="'/bendahara/bills/' + billId + '/cash'" method="POST">
                        @csrf

                        {{-- Pilihan tipe bayar --}}
                        <div class="grid grid-cols-3 gap-2 mb-4">
                            <button type="button" @click="payType = 'full'"
                                :class="payType === 'full' ? 'bg-green-600 text-white border-green-500' : 'bg-gray-800 text-gray-400 border-white/10'"
                                class="text-xs font-medium py-2 rounded-lg border transition-colors">
                                ✓ Lunas
                            </button>
                            <button type="button" @click="payType = 'partial'"
                                :class="payType === 'partial' ? 'bg-purple-600 text-white border-purple-500' : 'bg-gray-800 text-gray-400 border-white/10'"
                                class="text-xs font-medium py-2 rounded-lg border transition-colors">
                                💵 Cicil
                            </button>
                            @if($discounts->isNotEmpty())
                            <button type="button" @click="payType = 'scholarship'"
                                :class="payType === 'scholarship' ? 'bg-blue-600 text-white border-blue-500' : 'bg-gray-800 text-gray-400 border-white/10'"
                                class="text-xs font-medium py-2 rounded-lg border transition-colors">
                                🎓 Beasiswa
                            </button>
                            @endif
                        </div>

                        <input type="hidden" name="pay_type" :value="payType">

                        {{-- Lunas: langsung konfirmasi --}}
                        <div x-show="payType === 'full'" class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 mb-4">
                            <p class="text-sm text-green-400 text-center">
                                Akan mencatat pembayaran lunas<br>
                                <strong>Rp <span x-text="remaining.toLocaleString('id-ID')"></span></strong>
                            </p>
                        </div>

                        {{-- Cicil: input nominal --}}
                        <div x-show="payType === 'partial'" class="mb-4">
                            <label class="text-xs text-gray-400 mb-1 block">Nominal Cicilan (Rp) *</label>
                            <input type="number" name="amount" min="1" :max="remaining"
                                placeholder="Masukkan nominal yang dibayar"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            <p class="text-xs text-gray-500 mt-1">
                                Sisa setelah cicilan ini akan tetap tercatat sebagai tunggakan
                            </p>
                        </div>

                        {{-- Beasiswa --}}
                        <div x-show="payType === 'scholarship'" class="mb-4">
                            <label class="text-xs text-gray-400 mb-1 block">Pilih Beasiswa *</label>
                            <select name="discount_id" x-model="discountId"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                <option value="">-- Pilih beasiswa --</option>
                                @foreach($discounts as $disc)
                                    <option value="{{ $disc->id }}">
                                        {{ $disc->name }}
                                        ({{ $disc->discount_type === 'percent' ? $disc->discount_value . '%' : 'Rp ' . number_format($disc->discount_value, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                Pembayaran menggunakan beasiswa akan melunasi sisa tagihan
                            </p>
                        </div>

                        {{-- Catatan --}}
                        <div class="mb-4">
                            <label class="text-xs text-gray-400 mb-1 block">Catatan (opsional)</label>
                            <input type="text" name="cashier_notes" placeholder="Catatan pembayaran..."
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>

                        <button type="submit"
                            class="w-full py-2.5 rounded-lg text-sm font-semibold text-white transition-colors"
                            :class="{
                                'bg-green-600 hover:bg-green-700': payType === 'full',
                                'bg-purple-600 hover:bg-purple-700': payType === 'partial',
                                'bg-blue-600 hover:bg-blue-700': payType === 'scholarship'
                            }">
                            <span x-text="payType === 'full' ? 'Konfirmasi Lunas' : payType === 'scholarship' ? 'Catat via Beasiswa' : 'Catat Cicilan'"></span>
                        </button>
                    </form>
                </div>
            </template>
        </div>
    </div>

    <script>
    document.getElementById('modal-bayar').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
    </script>

</x-simans-layout>
