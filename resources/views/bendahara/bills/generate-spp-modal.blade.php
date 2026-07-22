{{-- Komponen Generate SPP Otomatis - di-include dari payment-types/index.blade.php --}}

{{-- Modal Generate SPP --}}
<div id="modal-generate-spp" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
    <div class="bg-white border border-gray-200 rounded-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-gray-900 font-semibold">Generate SPP Otomatis</h3>
                <p class="text-xs text-gray-500 mt-0.5">Buat tagihan untuk semua siswa aktif sekaligus</p>
            </div>
            <button onclick="document.getElementById('modal-generate-spp').style.display='none'" class="text-gray-500 hover:text-gray-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="bg-blue-500/5 border border-blue-500/15 rounded-xl px-3 py-2 mb-4 text-xs text-blue-300">
            Sistem akan membuat tagihan untuk <strong>semua siswa aktif</strong> berdasarkan tarif yang sudah diatur.
            Siswa yang sudah punya tagihan untuk periode ini akan dilewati otomatis.
        </div>

        <form method="POST" action="{{ route('bendahara.bills.generate-spp') }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Jenis Pembayaran *</label>
                    <select name="payment_type_id" required
                        class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        <option value="">-- Pilih jenis --</option>
                        @foreach($types as $t)
                            @if($t->is_active)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Tahun Ajaran *</label>
                    <select name="academic_year_id" required
                        class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>
                                {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Label Periode *</label>
                        <input type="text" name="period_label" required
                            placeholder="Juli 2026"
                            value="{{ now()->translatedFormat('F Y') }}"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Tanggal *</label>
                        <input type="date" name="period_date" required value="{{ date('Y-m-01') }}"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Jatuh Tempo</label>
                    <input type="date" name="due_date"
                        value="{{ date('Y-m-t') }}"
                        class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="button" onclick="document.getElementById('modal-generate-spp').style.display='none'"
                    class="flex-1 bg-white text-gray-400 text-sm py-2 rounded-lg">Batal</button>
                <button type="submit"
                    onclick="return confirm('Generate tagihan untuk semua siswa aktif?')"
                    class="flex-1 bg-emerald-700 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg">
                    Generate Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
