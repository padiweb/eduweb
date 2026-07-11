<x-simans-layout title="Pemasukan — {{ $fundSource->name }}">

    <div class="mb-6">
        <a href="{{ route('bendahara.fund-sources.index') }}" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-3 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-white">Pemasukan — {{ $fundSource->name }}</h1>
                <p class="text-gray-400 text-sm mt-0.5">{{ $fundSource->getTypeLabel() }} · Total: Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
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
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
        @if($fundSource->incomes->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-500">Belum ada pemasukan dari sumber ini.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Tanggal</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Keterangan</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Periode</th>
                        <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Jumlah</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Dicatat oleh</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($fundSource->incomes as $income)
                    <tr class="hover:bg-white/2 transition-colors">
                        <td class="px-4 py-3 text-gray-300">{{ $income->income_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <p class="text-white">{{ $income->description }}</p>
                            @if($income->reference_number)
                                <p class="text-xs text-gray-500">No: {{ $income->reference_number }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-400">{{ $income->period_label ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-green-400 font-semibold">{{ $income->amount_formatted }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $income->createdBy->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('bendahara.fund-sources.incomes.destroy', $income) }}"
                                onsubmit="return confirm('Hapus data pemasukan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-600 hover:text-red-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- MODAL: Catat Pemasukan --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-lg p-6">
            <h3 class="text-white font-semibold mb-4">Catat Pemasukan — {{ $fundSource->name }}</h3>
            <form method="POST" action="{{ route('bendahara.fund-sources.incomes.store', $fundSource) }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Keterangan *</label>
                        <input type="text" name="description" required placeholder="Pencairan BOS Triwulan I, Setoran SPP, dll"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Tahun Ajaran *</label>
                            <select name="academic_year_id" required class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                @foreach($academicYears as $y)
                                    <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>
                                        {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' (Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Tanggal *</label>
                            <input type="date" name="income_date" required value="{{ date('Y-m-d') }}"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Jumlah (Rp) *</label>
                        <input type="number" name="amount" required min="1" placeholder="10000000"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Label Periode</label>
                            <input type="text" name="period_label" placeholder="Triwulan I 2026"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">No. Referensi</label>
                            <input type="text" name="reference_number" placeholder="No. SK/Kuitansi"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Lampiran (SK/Bukti)</label>
                        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        <p class="text-xs text-gray-600 mt-1">JPG, PNG, PDF · Maks 5MB</p>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2 rounded-lg">Batal</button>
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 rounded-lg">Catat</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('modal-add').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
    </script>

</x-simans-layout>
