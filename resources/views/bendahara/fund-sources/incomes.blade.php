<x-simans-layout title="Pemasukan — {{ $fundSource->name }}">

    <div class="mb-6">
        <a href="{{ route('bendahara.fund-sources.index') }}" class="text-gray-500 hover:text-blue-600 text-sm flex items-center gap-1 mb-3 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali ke Sumber Dana
        </a>
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-900">{{ $fundSource->name }}</h1>
                    @if(!$fundSource->is_active)
                        <span class="text-xs bg-red-50 text-red-600 border border-red-200 px-2 py-0.5 rounded-full">Nonaktif</span>
                    @endif
                </div>
                <p class="text-gray-500 text-sm mt-0.5">
                    {{ $fundSource->getTypeLabel() }} ·
                    Total Pemasukan: <span class="text-green-600 font-medium">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
                </p>
            </div>
            <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Catat Pemasukan
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-600 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg px-4 py-3 mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="tbl-card">
        @if($fundSource->incomes->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-500">Belum ada pemasukan dari sumber ini.</p>
                <p class="text-gray-500 text-xs mt-1">Klik "Catat Pemasukan" untuk menambah.</p>
            </div>
        @else
            <div class="tbl-wrap">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Tanggal</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Keterangan</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Periode</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Jumlah</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Dicatat oleh</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($fundSource->incomes as $income)
                        <tr class="hover:bg-white/2 transition-colors">
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $income->income_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-gray-900">{{ $income->description }}</p>
                                @if($income->reference_number)
                                    <p class="text-xs text-gray-500">No: {{ $income->reference_number }}</p>
                                @endif
                                @if($income->notes)
                                    <p class="text-xs text-gray-500">{{ $income->notes }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ $income->period_label ?? '-' }}
                                <p class="text-xs text-gray-500">{{ $income->academicYear->name ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-right text-green-600 font-semibold whitespace-nowrap">
                                {{ $income->amount_formatted }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $income->createdBy->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openEdit(
                                        {{ $income->id }},
                                        '{{ addslashes($income->description) }}',
                                        {{ $income->amount }},
                                        '{{ $income->income_date->format('Y-m-d') }}',
                                        '{{ addslashes($income->period_label ?? '') }}',
                                        '{{ addslashes($income->reference_number ?? '') }}',
                                        '{{ addslashes($income->notes ?? '') }}'
                                    )" class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('bendahara.fund-sources.incomes.destroy', $income) }}"
                                        onsubmit="return confirm('Hapus data pemasukan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-gray-500 hover:text-red-600 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200">
                            <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-gray-500">Total</td>
                            <td class="px-4 py-3 text-right text-base font-bold text-green-600">
                                Rp {{ number_format($totalIncome, 0, ',', '.') }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    {{-- MODAL: Catat Pemasukan --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
        <div class="bg-white border border-gray-200 rounded-xl w-full max-w-lg p-6">
            <h3 class="text-gray-900 font-semibold mb-4">Catat Pemasukan — {{ $fundSource->name }}</h3>
            <form method="POST" action="{{ route('bendahara.fund-sources.incomes.store', $fundSource) }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Keterangan *</label>
                        <input type="text" name="description" required
                            placeholder="Pencairan BOS Triwulan I, Setoran SPP, dll"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Tahun Ajaran *</label>
                            <select name="academic_year_id" required
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                                @foreach($academicYears as $y)
                                    <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>
                                        {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' ✓' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Tanggal *</label>
                            <input type="date" name="income_date" required value="{{ date('Y-m-d') }}"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Jumlah (Rp) *</label>
                        <input type="number" name="amount" required min="1" placeholder="10000000"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Label Periode</label>
                            <input type="text" name="period_label" placeholder="Triwulan I 2026"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">No. Referensi</label>
                            <input type="text" name="reference_number" placeholder="No. SK / Kuitansi"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Opsional..."
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none resize-none"></textarea>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Lampiran (SK/Bukti)</label>
                        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, PDF · Maks 5MB</p>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="flex-1 bg-white hover:bg-gray-50 text-gray-600 text-sm font-medium py-2 rounded-lg">Batal</button>
                    <button type="submit"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 rounded-lg">Catat</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Edit Pemasukan --}}
    <div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
        <div class="bg-white border border-gray-200 rounded-xl w-full max-w-lg p-6">
            <h3 class="text-gray-900 font-semibold mb-4">Edit Pemasukan</h3>
            <form id="form-edit" method="POST" action="">
                @csrf @method('PUT')
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Keterangan *</label>
                        <input type="text" id="edit-description" name="description" required
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Jumlah (Rp) *</label>
                            <input type="number" id="edit-amount" name="amount" required min="1"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Tanggal *</label>
                            <input type="date" id="edit-date" name="income_date" required
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Label Periode</label>
                            <input type="text" id="edit-period" name="period_label"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">No. Referensi</label>
                            <input type="text" id="edit-ref" name="reference_number"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Catatan</label>
                        <textarea id="edit-notes" name="notes" rows="2"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="flex-1 bg-white hover:bg-gray-50 text-gray-600 text-sm font-medium py-2 rounded-lg">Batal</button>
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEdit(id, description, amount, date, period, ref, notes) {
        document.getElementById('form-edit').action = `/bendahara/fund-income/${id}`;
        document.getElementById('edit-description').value = description;
        document.getElementById('edit-amount').value      = amount;
        document.getElementById('edit-date').value        = date;
        document.getElementById('edit-period').value      = period;
        document.getElementById('edit-ref').value         = ref;
        document.getElementById('edit-notes').value       = notes;
        document.getElementById('modal-edit').classList.remove('hidden');
    }
    ['modal-add', 'modal-edit'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
    });
    </script>

</x-simans-layout>
