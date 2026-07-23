<x-simans-layout title="Edit Tagihan">

    <div class="mb-6">
        <a href="{{ route('bendahara.bills.show', $bill) }}" class="text-gray-500 hover:text-blue-600 text-sm flex items-center gap-1 mb-3 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-xl font-bold text-gray-900">Edit Tagihan</h1>
        <p class="text-gray-500 text-sm mt-0.5">{{ $bill->student->name }} · {{ $bill->paymentType->name }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Form edit --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Ubah Data Tagihan</h2>
            <form method="POST" action="{{ route('bendahara.bills.update', $bill) }}">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Label Periode *</label>
                            <input type="text" name="period_label" required
                                value="{{ old('period_label', $bill->period_label) }}"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Tanggal Periode *</label>
                            <input type="date" name="period_date" required
                                value="{{ old('period_date', \Carbon\Carbon::parse($bill->period_date)->format('Y-m-d')) }}"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Jatuh Tempo</label>
                        <input type="date" name="due_date"
                            value="{{ old('due_date', $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('Y-m-d') : '') }}"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Nominal Tarif Dasar (Rp) *</label>
                        <input type="number" name="amount_base" required min="0"
                            value="{{ old('amount_base', $bill->amount_base) }}"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Diskon/Beasiswa (Rp)</label>
                        <input type="number" name="amount_discount" min="0"
                            value="{{ old('amount_discount', $bill->amount_discount) }}"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        <p class="text-xs text-gray-500 mt-1">Total tagihan = Tarif dasar - Diskon</p>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <a href="{{ route('bendahara.bills.show', $bill) }}"
                        class="flex-1 text-center bg-white hover:bg-gray-50 text-gray-600 text-sm font-medium py-2.5 rounded-lg transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2.5 rounded-lg transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Info + Hapus --}}
        <div class="space-y-4">
            {{-- Ringkasan --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Ringkasan Tagihan</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Siswa</span>
                        <span class="text-gray-900">{{ $bill->student->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jenis</span>
                        <span class="text-gray-900">{{ $bill->paymentType->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tarif saat ini</span>
                        <span class="text-gray-900">Rp {{ number_format($bill->amount_base, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Diskon saat ini</span>
                        <span class="text-green-600">Rp {{ number_format($bill->amount_discount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2 font-medium">
                        <span class="text-gray-600">Total tagihan</span>
                        <span class="text-gray-900">Rp {{ number_format($bill->amount_billed, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        @php $labels = ['unpaid'=>'Belum bayar','partial'=>'Cicilan','paid'=>'Lunas','waived'=>'Dibebaskan']; @endphp
                        <span class="text-gray-900">{{ $labels[$bill->status] ?? $bill->status }}</span>
                    </div>
                </div>
            </div>

            {{-- Hapus --}}
            <div class="bg-white border border-red-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-red-600 mb-1">Hapus Tagihan</h3>
                <p class="text-xs text-gray-500 mb-3">
                    Tagihan yang sudah ada pembayaran approved tidak dapat dihapus. Tindakan ini permanen.
                </p>
                <form method="POST" action="{{ route('bendahara.bills.destroy', $bill) }}"
                    onsubmit="return confirm('Yakin hapus tagihan ini? Tindakan tidak dapat dibatalkan.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-full bg-red-50 hover:bg-red-50 border border-red-200 text-red-600 text-sm font-medium py-2 rounded-lg transition-colors">
                        Hapus Tagihan Ini
                    </button>
                </form>
            </div>
        </div>
    </div>

</x-simans-layout>
