<x-simans-layout title="Beasiswa & Keringanan">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Beasiswa & Keringanan</h1>
            <p class="text-gray-500 text-sm mt-0.5">Kelola diskon pembayaran per siswa</p>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-gray-900 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Beasiswa
        </button>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIS siswa..."
            class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 w-48 focus:border-purple-500 focus:outline-none">
        <select name="year" class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
            <option value="">Semua tahun</option>
            @foreach($academicYears as $y)
                <option value="{{ $y->id }}" {{ request('year') == $y->id ? 'selected' : '' }}>
                    {{ $y->name }} Sem {{ $y->semester }}
                </option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-500 cursor-pointer">
            <input type="checkbox" name="active" value="1" {{ request('active') ? 'checked' : '' }} class="text-purple-500">
            Aktif saja
        </label>
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-gray-900 text-sm px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['search','year','active']))
            <a href="{{ route('bendahara.discounts.index') }}" class="text-gray-500 hover:text-gray-900 text-sm px-3 py-2">Reset</a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($discounts->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-400">Belum ada data beasiswa/keringanan.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs text-gray-400 font-medium px-4 py-3">Siswa</th>
                            <th class="text-left text-xs text-gray-400 font-medium px-4 py-3">Nama Beasiswa</th>
                            <th class="text-left text-xs text-gray-400 font-medium px-4 py-3">Jenis Tagihan</th>
                            <th class="text-center text-xs text-gray-400 font-medium px-4 py-3">Potongan</th>
                            <th class="text-left text-xs text-gray-400 font-medium px-4 py-3">Masa Berlaku</th>
                            <th class="text-center text-xs text-gray-400 font-medium px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($discounts as $disc)
                        @php $active = $disc->isActiveNow(); @endphp
                        <tr class="hover:bg-white/2 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-gray-900 font-medium">{{ $disc->student->name ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ $disc->student->nis ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-gray-600">{{ $disc->name }}</p>
                                @if($disc->notes)
                                    <p class="text-xs text-gray-400">{{ $disc->notes }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ $disc->paymentType->name ?? 'Semua jenis' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-green-400 font-semibold">{{ $disc->getDiscountLabel() }}</span>
                                <p class="text-xs text-gray-400">{{ $disc->discount_type === 'percent' ? 'Persen' : 'Nominal' }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $disc->valid_from->format('d/m/Y') }}
                                @if($disc->valid_until)
                                    s/d {{ $disc->valid_until->format('d/m/Y') }}
                                @else
                                    s/d selesai
                                @endif
                                <p class="text-gray-400">{{ $disc->academicYear->name ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($active)
                                    <span class="text-xs bg-green-500/10 text-green-400 border border-green-500/20 px-2.5 py-0.5 rounded-full">Aktif</span>
                                @else
                                    <span class="text-xs bg-gray-500/10 text-gray-500 border border-gray-500/20 px-2.5 py-0.5 rounded-full">Tidak aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('bendahara.discounts.destroy', $disc) }}"
                                    onsubmit="return confirm('Hapus beasiswa ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-400 transition-colors">
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
            </div>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $discounts->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL: Tambah Beasiswa --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
        <div class="bg-white border border-gray-200 rounded-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto"
             x-data="{
                searchQuery: '',
                results: [],
                selectedId: '',
                selectedName: '',
                selectedNis: '',
                selectedClass: '',
                discountType: 'percent',
                loading: false,
                async searchStudents() {
                    if (this.searchQuery.length < 2) { this.results = []; return; }
                    this.loading = true;
                    try {
                        const res = await fetch('{{ route('bendahara.discounts.search') }}?q=' + encodeURIComponent(this.searchQuery), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        this.results = await res.json();
                    } catch(e) {
                        console.error(e);
                        this.results = [];
                    }
                    this.loading = false;
                },
                selectStudent(s) {
                    this.selectedId    = s.id;
                    this.selectedName  = s.name;
                    this.selectedNis   = s.nis;
                    this.selectedClass = s.classroom + (s.major && s.major !== '-' ? ' / ' + s.major : '');
                    this.results       = [];
                    this.searchQuery   = '';
                },
                clearStudent() {
                    this.selectedId = ''; this.selectedName = '';
                    this.selectedNis = ''; this.selectedClass = '';
                }
             }">

            <h3 class="text-gray-900 font-semibold mb-4">Tambah Beasiswa / Keringanan</h3>
            <form method="POST" action="{{ route('bendahara.discounts.store') }}">
                @csrf
                <div class="space-y-3">

                    {{-- Cari siswa --}}
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Siswa *</label>

                        {{-- Input pencarian --}}
                        <div x-show="!selectedId">
                            <input type="text"
                                x-model="searchQuery"
                                @input.debounce.400ms="searchStudents()"
                                placeholder="Ketik nama atau NIS siswa..."
                                autocomplete="off"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">

                            {{-- Loading --}}
                            <p x-show="loading" class="text-xs text-gray-400 mt-1">Mencari...</p>

                            {{-- Hasil --}}
                            <div x-show="results.length > 0 && !loading"
                                class="mt-1 bg-white border border-gray-200 rounded-lg overflow-hidden max-h-48 overflow-y-auto">
                                <template x-for="s in results" :key="s.id">
                                    <div @click="selectStudent(s)"
                                        class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-0">
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-900 font-medium" x-text="s.name"></p>
                                            <p class="text-xs text-gray-500" x-text="s.nis + ' · ' + s.classroom"></p>
                                        </div>
                                        <svg class="w-4 h-4 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            {{-- Tidak ketemu --}}
                            <p x-show="searchQuery.length >= 2 && results.length === 0 && !loading"
                                class="text-xs text-gray-400 mt-1">Siswa tidak ditemukan</p>
                        </div>

                        {{-- Siswa terpilih --}}
                        <div x-show="selectedId"
                            class="bg-purple-500/10 border border-purple-500/20 rounded-lg px-3 py-2.5 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-900 font-medium" x-text="selectedName"></p>
                                <p class="text-xs text-gray-500" x-text="selectedNis + ' · ' + selectedClass"></p>
                            </div>
                            <button type="button" @click="clearStudent()" class="text-gray-500 hover:text-red-400 ml-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <input type="hidden" name="user_id" x-model="selectedId">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Nama Beasiswa/Keringanan *</label>
                        <input type="text" name="name" required placeholder="Contoh: Beasiswa Yayasan, Keringanan Ekonomi"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Tahun Ajaran *</label>
                        <select name="academic_year_id" required
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            @foreach($academicYears as $y)
                                <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>
                                    {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Berlaku untuk jenis tagihan</label>
                        <select name="payment_type_id"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            <option value="">Semua jenis tagihan</option>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Kosongkan = berlaku untuk semua jenis pembayaran</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Tipe Diskon *</label>
                            <select name="discount_type" required x-model="discountType"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                <option value="percent">Persen (%)</option>
                                <option value="fixed">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">
                                Nilai *
                                <span x-text="discountType === 'percent' ? '(maks 100%)' : '(Rp)'"></span>
                            </label>
                            <input type="number" name="discount_value" required min="1"
                                :max="discountType === 'percent' ? 100 : 9999999999"
                                :placeholder="discountType === 'percent' ? '25' : '500000'"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Berlaku Dari *</label>
                            <input type="date" name="valid_from" required value="{{ date('Y-m-01') }}"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Berlaku Sampai</label>
                            <input type="date" name="valid_until"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            <p class="text-xs text-gray-400 mt-0.5">Kosong = tidak ada batas</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Keterangan</label>
                        <textarea name="notes" rows="2" placeholder="Sumber beasiswa, alasan keringanan, dll..."
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none resize-none"></textarea>
                    </div>
                </div>

                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="flex-1 bg-white hover:bg-gray-100 text-gray-600 text-sm font-medium py-2.5 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" :disabled="!selectedId"
                        :class="selectedId ? 'bg-purple-600 hover:bg-purple-700 cursor-pointer' : 'bg-gray-100 cursor-not-allowed'"
                        class="flex-1 text-gray-900 text-sm font-medium py-2.5 rounded-lg transition-colors">
                        Simpan
                    </button>
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
