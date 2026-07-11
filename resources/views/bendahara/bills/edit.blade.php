<x-simans-layout title="Edit Tagihan">

    <div class="mb-6">
        <a href="{{ route('bendahara.bills.show', $bill) }}" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-3 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-xl font-bold text-white">Edit Tagihan</h1>
        <p class="text-gray-400 text-sm mt-0.5">
            {{ $bill->student->name }} · {{ $bill->paymentType->name }}
        </p>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-lg px-4 py-3 mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="max-w-lg">
        <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
            <form method="POST" action="{{ route('bendahara.bills.update', $bill) }}">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Label Periode *</label>
                            <input type="text" name="period_label" required
                                value="{{ old('period_label', $bill->period_label) }}"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Tanggal Periode *</label>
                            <input type="date" name="period_date" required
                                value="{{ old('period_date', $bill->period_date->format('Y-m-d')) }}"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Jatuh Tempo</label>
                        <input type="date" name="due_date"
                            value="{{ old('due_date', $bill->due_date?->format('Y-m-d')) }}"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Nominal Tagihan (Rp) *</label>
                        <input type="number" name="amount_billed" required min="0"
                            value="{{ old('amount_billed', $bill->amount_billed) }}"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        <p class="text-xs text-gray-500 mt-1">Tarif dasar: Rp {{ number_format($bill->amount_base, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Diskon/Beasiswa (Rp)</label>
                        <input type="number" name="amount_discount" min="0"
                            value="{{ old('amount_discount', $bill->amount_discount) }}"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <a href="{{ route('bendahara.bills.show', $bill) }}"
                        class="flex-1 text-center bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2.5 rounded-lg transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2.5 rounded-lg transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Hapus tagihan --}}
        <div class="bg-gray-900 border border-red-500/20 rounded-xl p-5 mt-4">
            <h3 class="text-sm font-semibold text-red-400 mb-1">Hapus Tagihan</h3>
            <p class="text-xs text-gray-500 mb-3">Tagihan yang sudah ada pembayaran approved tidak dapat dihapus.</p>
            <form method="POST" action="{{ route('bendahara.bills.destroy', $bill) }}"
                onsubmit="return confirm('Yakin hapus tagihan ini? Tindakan ini tidak dapat dibatalkan.')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="w-full bg-red-600/20 hover:bg-red-600/40 border border-red-500/30 text-red-400 text-sm font-medium py-2 rounded-lg transition-colors">
                    Hapus Tagihan
                </button>
            </form>
        </div>
    </div>

</x-simans-layout>