<x-simans-layout title="Anggota Program Beasiswa">

    <div class="mb-6">
        <a href="{{ route('bendahara.discount-programs.index') }}"
            class="text-gray-500 hover:text-gray-900 text-sm flex items-center gap-1 mb-3 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $program->name }}</h1>
                <p class="text-gray-500 text-sm mt-0.5">
                    {{ $program->academicYear->name }} ·
                    {{ $program->discount_type === 'percent' ? $program->default_value.'%' : 'Rp '.number_format($program->default_value,0,',','.') }} default
                    @if($program->paymentType) · {{ $program->paymentType->name }} @endif
                </p>
            </div>
            <button onclick="document.getElementById('modal-add-member').style.display='flex'"
                class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-gray-900 text-sm font-medium px-4 py-2 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Tambah Siswa
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif

    {{-- Info --}}
    <div class="bg-blue-500/5 border border-blue-500/15 rounded-xl px-4 py-3 mb-5 text-xs text-blue-300">
        Nilai default program: <strong>{{ $program->default_value_formatted }}</strong>.
        Isi kolom Override jika siswa tertentu punya nominal berbeda — kosongkan untuk pakai nilai default.
        Setelah semua siswa ditambahkan, klik <strong>Terapkan</strong> di halaman program.
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($program->members->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-400">Belum ada siswa dalam program ini.</p>
                <p class="text-gray-400 text-xs mt-1">Klik Tambah Siswa untuk menambahkan massal.</p>
            </div>
        @else
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <p class="text-xs text-gray-400">{{ $program->members->count() }} siswa dalam program ini</p>
                <form method="POST" action="{{ route('bendahara.discount-programs.apply', $program) }}">
                    @csrf
                    <button type="submit"
                        onclick="return confirm('Terapkan ke semua {{ $program->members->count() }} siswa?')"
                        class="text-xs bg-green-600/10 hover:bg-green-600/20 border border-green-500/20 text-green-400 px-3 py-1.5 rounded-lg transition-colors">
                        Terapkan Semua ke Tagihan
                    </button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs text-gray-400 font-medium px-4 py-3">Siswa</th>
                            <th class="text-center text-xs text-gray-400 font-medium px-4 py-3">Nilai Default</th>
                            <th class="text-center text-xs text-gray-400 font-medium px-4 py-3">Override</th>
                            <th class="text-center text-xs text-gray-400 font-medium px-4 py-3">Efektif</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($program->members as $member)
                        <tr class="hover:bg-white/2 transition-colors" x-data="{ editing: false }">
                            <td class="px-4 py-3">
                                <p class="text-gray-900 font-medium">{{ $member->student->name }}</p>
                                <p class="text-xs text-gray-400">{{ $member->student->nis ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 text-xs">
                                {{ $program->default_value_formatted }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div x-show="!editing">
                                    <span class="text-xs {{ $member->override_value ? 'text-amber-400' : 'text-gray-400' }}">
                                        {{ $member->override_value
                                            ? ($program->discount_type === 'percent' ? $member->override_value.'%' : 'Rp '.number_format($member->override_value,0,',','.'))
                                            : '—' }}
                                    </span>
                                </div>
                                <div x-show="editing" x-cloak>
                                    <form method="POST" action="{{ route('bendahara.discount-programs.member.update', $member) }}"
                                        class="flex items-center gap-1">
                                        @csrf @method('PATCH')
                                        <input type="number" name="override_value" min="0"
                                            value="{{ $member->override_value }}"
                                            placeholder="{{ $program->default_value }}"
                                            class="w-24 bg-white border border-gray-200 text-gray-700 text-xs rounded px-2 py-1 focus:border-purple-500 focus:outline-none">
                                        <button type="submit" class="text-xs text-green-400 hover:text-green-300 px-2">Simpan</button>
                                    </form>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-purple-400 text-xs">
                                {{ $member->effective_value_formatted }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    <button type="button" @click="editing = !editing"
                                        class="text-xs text-gray-400 hover:text-gray-900 transition-colors">
                                        <span x-text="editing ? 'Batal' : 'Override'"></span>
                                    </button>
                                    <form method="POST" action="{{ route('bendahara.discount-programs.member.remove', $member) }}"
                                        onsubmit="return confirm('Hapus {{ $member->student->name }} dari program?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-gray-400 hover:text-red-400 transition-colors">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- MODAL Tambah Siswa --}}
    <div id="modal-add-member" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
        x-data="{
            search: '',
            results: [],
            selected: {},
            async doSearch() {
                if (this.search.length < 2) { this.results = []; return; }
                const res = await fetch('{{ route('bendahara.discount-programs.search', $program) }}?q=' + encodeURIComponent(this.search), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.results = await res.json();
            },
            toggle(s) {
                if (this.selected[s.id]) delete this.selected[s.id];
                else this.selected[s.id] = { ...s, override: '' };
            },
            get selectedList() { return Object.values(this.selected); }
        }">
        <div class="bg-white border border-gray-200 rounded-2xl w-full max-w-lg p-6 max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-900 font-semibold">Tambah Siswa ke Program</h3>
                <button onclick="document.getElementById('modal-add-member').style.display='none'"
                    class="text-gray-400 hover:text-gray-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Search --}}
            <input type="text" x-model="search" @input.debounce.400ms="doSearch()"
                placeholder="Cari nama atau NIS siswa..."
                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none mb-3">

            {{-- Hasil search --}}
            <div x-show="results.length > 0" class="border border-gray-200 rounded-lg overflow-hidden mb-3 max-h-48 overflow-y-auto">
                <template x-for="s in results" :key="s.id">
                    <div @click="toggle(s)"
                        :class="selected[s.id] ? 'bg-purple-500/10 border-l-2 border-purple-500' : 'hover:bg-gray-50'"
                        class="flex items-center gap-3 px-3 py-2.5 cursor-pointer border-b border-gray-200 last:border-0">
                        <div class="w-5 h-5 rounded border flex items-center justify-center flex-shrink-0"
                            :class="selected[s.id] ? 'bg-purple-600 border-purple-600' : 'border-gray-600'">
                            <svg x-show="selected[s.id]" class="w-3 h-3 text-gray-900" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900" x-text="s.name"></p>
                            <p class="text-xs text-gray-400" x-text="s.nis + ' · ' + s.classroom"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Dipilih --}}
            <div x-show="selectedList.length > 0">
                <p class="text-xs text-gray-500 mb-2">
                    <span x-text="selectedList.length"></span> siswa dipilih — isi override jika nominal berbeda:
                </p>
                <form method="POST" action="{{ route('bendahara.discount-programs.members.add', $program) }}">
                    @csrf
                    <div class="space-y-1.5 max-h-40 overflow-y-auto mb-4">
                        <template x-for="s in selectedList" :key="s.id">
                            <div class="flex items-center gap-2">
                                <input type="hidden" :name="'student_ids[]'" :value="s.id">
                                <span class="text-xs text-gray-900 flex-1 truncate" x-text="s.name"></span>
                                <input type="number" :name="'overrides[' + s.id + ']'"
                                    x-model="s.override" min="0"
                                    placeholder="Default: {{ $program->default_value }}"
                                    class="w-32 bg-white border border-gray-200 text-gray-700 text-xs rounded px-2 py-1.5 focus:border-purple-500 focus:outline-none">
                                <span class="text-xs text-gray-400">{{ $program->discount_type === 'percent' ? '%' : 'Rp' }}</span>
                            </div>
                        </template>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-add-member').style.display='none'"
                            class="flex-1 bg-white text-gray-600 text-sm py-2 rounded-lg">Batal</button>
                        <button type="submit"
                            class="flex-1 bg-purple-600 hover:bg-purple-700 text-gray-900 text-sm font-medium py-2 rounded-lg">
                            Tambahkan
                        </button>
                    </div>
                </form>
            </div>

            <div x-show="selectedList.length === 0 && results.length === 0 && search.length < 2"
                class="text-center text-gray-400 text-xs py-4">
                Ketik minimal 2 huruf untuk mencari siswa
            </div>
        </div>
    </div>

    <script>
    document.getElementById('modal-add-member').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
    </script>

</x-simans-layout>
