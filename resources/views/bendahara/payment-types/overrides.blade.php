<x-simans-layout title="Override Tarif Per Siswa">

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('bendahara.bills.create') }}" class="text-gray-500 hover:text-gray-900 text-sm flex items-center gap-1 mb-2 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                Kembali
            </a>
            <h1 class="text-xl font-bold text-gray-900">Override Tarif Per Siswa</h1>
            <p class="text-gray-500 text-sm mt-0.5">Tarif khusus untuk siswa tertentu — berbeda dari tarif kelas</p>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Override
        </button>
    </div>

    {{-- Info --}}
    <div class="bg-blue-500/5 border border-blue-500/15 rounded-xl px-4 py-3 mb-5 text-xs text-blue-300">
        Override tarif berlaku <strong>lebih utama</strong> dari tarif kelas/jurusan.
        Jika siswa punya override, tarif override yang dipakai saat membuat tagihan.
        Beasiswa/potongan tetap dihitung setelah override.
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($overrides->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-500">Belum ada override tarif.</p>
                <p class="text-gray-500 text-xs mt-1">Override hanya diperlukan jika ada siswa dengan tarif berbeda dari kelasnya.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Siswa</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Jenis Tagihan</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Tahun Ajaran</th>
                            <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Tarif Override</th>
                            <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Alasan</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($overrides as $ov)
                        <tr class="hover:bg-white/2 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-gray-900 font-medium">{{ $ov->student->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $ov->student->nis ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-400">{{ $ov->paymentType->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $ov->academicYear->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-blue-600">{{ $ov->amount_formatted }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $ov->reason ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('bendahara.bills.overrides.destroy', $ov) }}"
                                    onsubmit="return confirm('Hapus override tarif ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-500 hover:text-red-400 transition-colors">
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
            <div class="px-4 py-3 border-t border-gray-200">{{ $overrides->links() }}</div>
        @endif
    </div>

    {{-- MODAL tambah override --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
        x-data="{
            search: '',
            results: [],
            selectedId: '',
            selectedName: '',
            selectedNis: '',
            async searchStudents() {
                if (this.search.length < 2) { this.results = []; return; }
                const res = await fetch('{{ route('bendahara.discounts.search') }}?q=' + encodeURIComponent(this.search), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.results = await res.json();
            },
            selectStudent(s) {
                this.selectedId   = s.id;
                this.selectedName = s.name;
                this.selectedNis  = s.nis;
                this.results      = [];
                this.search       = '';
            },
            clearStudent() { this.selectedId = ''; this.selectedName = ''; }
        }">
        <div class="bg-white border border-gray-200 rounded-xl w-full max-w-md p-6">
            <h3 class="text-gray-900 font-semibold mb-4">Tambah Override Tarif</h3>
            <form method="POST" action="{{ route('bendahara.bills.overrides.store') }}">
                @csrf
                <div class="space-y-3">
                    {{-- Cari siswa --}}
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Siswa *</label>
                        <div x-show="!selectedId">
                            <input type="text" x-model="search" @input.debounce.400ms="searchStudents()"
                                placeholder="Ketik nama atau NIS..."
                                autocomplete="off"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            <div x-show="results.length > 0" class="mt-1 bg-white border border-gray-200 rounded-lg overflow-hidden max-h-40 overflow-y-auto">
                                <template x-for="s in results" :key="s.id">
                                    <div @click="selectStudent(s)" class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-0">
                                        <div>
                                            <p class="text-sm text-gray-900" x-text="s.name"></p>
                                            <p class="text-xs text-gray-500" x-text="s.nis + ' · ' + s.classroom"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div x-show="selectedId" class="bg-blue-500/10 border border-blue-200 rounded-lg px-3 py-2 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-900" x-text="selectedName"></p>
                                <p class="text-xs text-gray-500" x-text="selectedNis"></p>
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
                        <label class="text-xs text-gray-500 mb-1 block">Jenis Tagihan *</label>
                        <select name="payment_type_id" required class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            <option value="">-- Pilih jenis --</option>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Tahun Ajaran *</label>
                        <select name="academic_year_id" required class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            @foreach($academicYears as $y)
                                <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>
                                    {{ $y->name }} Sem {{ $y->semester }}{{ $y->is_active ? ' ✓' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Tarif Override (Rp) *</label>
                        <input type="number" name="amount" required min="0" placeholder="250000"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        <p class="text-xs text-gray-500 mt-1">Ini menggantikan tarif kelas, bukan memotong</p>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Alasan</label>
                        <input type="text" name="reason" placeholder="Siswa pindahan, kesepakatan khusus, dll"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="flex-1 bg-white hover:bg-gray-50 text-gray-400 text-sm font-medium py-2 rounded-lg">Batal</button>
                    <button type="submit" :disabled="!selectedId"
                        :class="selectedId ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-100 cursor-not-allowed'"
                        class="flex-1 text-gray-900 text-sm font-medium py-2 rounded-lg transition-colors">
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
