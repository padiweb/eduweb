<x-simans-layout title="Catat Pengeluaran">

    <div class="mb-6">
        <a href="{{ route('bendahara.expenses.index') }}" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-3 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-xl font-bold text-white">Catat Pengeluaran</h1>
        <p class="text-gray-400 text-sm mt-0.5">Input pengeluaran dari sumber dana manapun</p>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-lg px-4 py-3 mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('bendahara.expenses.store') }}" enctype="multipart/form-data" x-data="{ catId: '', needApproval: false }">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="space-y-4">
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-4">Detail Pengeluaran</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Keterangan *</label>
                            <input type="text" name="description" required value="{{ old('description') }}"
                                placeholder="Gaji Juli 2026, Pembelian ATK, dll"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block">Sumber Dana *</label>
                                <select name="fund_source_id" required class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                    <option value="">-- Pilih sumber --</option>
                                    @foreach($sources as $s)
                                        <option value="{{ $s->id }}" {{ old('fund_source_id')==$s->id ? 'selected':'' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block">Kategori *</label>
                                <select name="expense_category_id" required
                                    class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                    <option value="">-- Pilih kategori --</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}"
                                            data-threshold="{{ $c->approval_threshold }}"
                                            data-requires="{{ $c->requires_approval ? '1' : '0' }}"
                                            {{ old('expense_category_id')==$c->id ? 'selected':'' }}>
                                            {{ $c->name }}{{ $c->requires_approval ? ' ⚠' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Jumlah (Rp) *</label>
                            <input type="number" name="amount" required min="1" value="{{ old('amount') }}"
                                placeholder="500000"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block">Tanggal *</label>
                                <input type="date" name="expense_date" required value="{{ old('expense_date', date('Y-m-d')) }}"
                                    class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block">Tahun Ajaran *</label>
                                <select name="academic_year_id" required class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                    @foreach($academicYears as $y)
                                        <option value="{{ $y->id }}" {{ $y->is_active ? 'selected':'' }}>
                                            {{ $y->name }} S{{ $y->semester }}{{ $y->is_active ? ' ✓':'' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block">Label Periode</label>
                                <input type="text" name="period_label" value="{{ old('period_label') }}" placeholder="Juli 2026"
                                    class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block">No. Referensi</label>
                                <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="No. Nota/SK"
                                    class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Catatan</label>
                            <textarea name="notes" rows="2" placeholder="Opsional..."
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none resize-none">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-4">Lampiran</h2>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Bukti / Nota / SK</label>
                        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        <p class="text-xs text-gray-600 mt-1">JPG, PNG, PDF · Maks 5MB</p>
                    </div>
                </div>

                {{-- Info approval --}}
                <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-amber-400 mb-2">⚠ Informasi Approval</h3>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Pengeluaran dengan kategori yang membutuhkan approval <strong>akan otomatis masuk ke antrian persetujuan kepala sekolah</strong>.
                        Pengeluaran kecil (di bawah batas nominal) langsung disetujui otomatis.
                    </p>
                    @if($categories->where('requires_approval', true)->isNotEmpty())
                        <div class="mt-3 space-y-1">
                            <p class="text-xs text-gray-500 font-medium">Kategori yang butuh approval:</p>
                            @foreach($categories->where('requires_approval', true) as $c)
                                <p class="text-xs text-gray-600">
                                    · {{ $c->name }}
                                    @if($c->approval_threshold > 0)
                                        ≥ Rp {{ number_format($c->approval_threshold, 0, ',', '.') }}
                                    @else
                                        (semua nominal)
                                    @endif
                                </p>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex gap-3 mt-5">
            <a href="{{ route('bendahara.expenses.index') }}"
                class="px-6 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium rounded-lg transition-colors">Batal</a>
            <button type="submit"
                class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                Simpan Pengeluaran
            </button>
        </div>
    </form>

</x-simans-layout>
