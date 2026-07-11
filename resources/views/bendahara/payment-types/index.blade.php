<x-simans-layout title="Jenis & Tarif Pembayaran">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-white">Jenis & Tarif Pembayaran</h1>
            <p class="text-gray-400 text-sm mt-0.5">Setup jenis pembayaran dan tarif per kelas/jurusan</p>
        </div>
        <button onclick="document.getElementById('modal-add-type').classList.remove('hidden')"
            class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Jenis
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-lg px-4 py-3 mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    @forelse($types as $type)
    <div class="bg-gray-900 border border-white/5 rounded-xl mb-4 overflow-hidden" x-data="{ open: false }">
        <div class="flex items-center gap-4 px-5 py-4 cursor-pointer" @click="open = !open">
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <span class="text-white font-medium">{{ $type->name }}</span>
                    @if($type->code)
                        <span class="text-xs text-gray-500 bg-gray-800 px-2 py-0.5 rounded">{{ $type->code }}</span>
                    @endif
                    @if(!$type->is_active)
                        <span class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 px-2 py-0.5 rounded">Nonaktif</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $type->category_label }} · {{ $type->period_type_label }} · {{ $type->bills_count }} tagihan
                </p>
            </div>
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('bendahara.payment-types.toggle', $type) }}" onclick="event.stopPropagation()">
                    @csrf @method('PATCH')
                    <button type="submit" class="text-xs {{ $type->is_active ? 'text-green-400 hover:text-red-400' : 'text-gray-500 hover:text-green-400' }} transition-colors">
                        {{ $type->is_active ? 'Aktif' : 'Nonaktif' }}
                    </button>
                </form>
                <button onclick="event.stopPropagation(); openEditType({{ $type->id }}, '{{ addslashes($type->name) }}', '{{ $type->code }}', '{{ $type->category }}', '{{ $type->period_type }}', '{{ addslashes($type->description ?? '') }}')"
                    class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                    </svg>
                </button>
                <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                </svg>
            </div>
        </div>

        <div x-show="open" x-cloak class="border-t border-white/5 px-5 py-4">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Tarif</p>
                <button onclick="openAddRate({{ $type->id }})"
                    class="text-xs text-purple-400 hover:text-purple-300 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Tambah Tarif
                </button>
            </div>

            @if($type->rates->isEmpty())
                <p class="text-sm text-gray-600 italic">Belum ada tarif. Tambah tarif agar bisa membuat tagihan.</p>
            @else
                <div class="space-y-2">
                    @foreach($type->rates as $rate)
                    <div class="flex items-center justify-between bg-gray-800 rounded-lg px-4 py-2.5">
                        <div>
                            <span class="text-sm text-white">{{ $rate->getScopeLabel() }}</span>
                            <span class="text-xs text-gray-500 ml-2">{{ $rate->academicYear->name ?? '-' }} Sem {{ $rate->academicYear->semester ?? '' }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-green-400">Rp {{ number_format($rate->amount, 0, ',', '.') }}</span>
                            <form method="POST" action="{{ route('bendahara.payment-rates.destroy', $rate) }}"
                                onsubmit="return confirm('Hapus tarif ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-600 hover:text-red-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @empty
        <div class="bg-gray-900 border border-white/5 rounded-xl px-5 py-12 text-center">
            <p class="text-gray-500">Belum ada jenis pembayaran. Klik "Tambah Jenis" untuk mulai.</p>
        </div>
    @endforelse

    {{-- MODAL: Tambah Jenis --}}
    <div id="modal-add-type" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-md p-6">
            <h3 class="text-white font-semibold mb-4">Tambah Jenis Pembayaran</h3>
            <form method="POST" action="{{ route('bendahara.payment-types.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Nama *</label>
                        <input type="text" name="name" required placeholder="Contoh: SPP Bulanan"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Kode (opsional)</label>
                            <input type="text" name="code" placeholder="SPP"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Kategori *</label>
                            <select name="category" required
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                <option value="spp">SPP</option>
                                <option value="ujian">Ujian</option>
                                <option value="kegiatan">Kegiatan</option>
                                <option value="seragam">Seragam/Buku</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Periode *</label>
                        <select name="period_type" required
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            <option value="monthly">Bulanan</option>
                            <option value="semester">Per Semester</option>
                            <option value="once">Sekali Bayar</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Keterangan</label>
                        <textarea name="description" rows="2" placeholder="Opsional..."
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-add-type').classList.add('hidden')"
                        class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2 rounded-lg transition-colors">Batal</button>
                    <button type="submit"
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2 rounded-lg transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Edit Jenis --}}
    <div id="modal-edit-type" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-md p-6">
            <h3 class="text-white font-semibold mb-4">Edit Jenis Pembayaran</h3>
            <form id="form-edit-type" method="POST" action="">
                @csrf @method('PUT')
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Nama *</label>
                        <input type="text" id="edit-name" name="name" required
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Kode</label>
                            <input type="text" id="edit-code" name="code"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Kategori *</label>
                            <select id="edit-category" name="category" required
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                <option value="spp">SPP</option>
                                <option value="ujian">Ujian</option>
                                <option value="kegiatan">Kegiatan</option>
                                <option value="seragam">Seragam/Buku</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Periode *</label>
                        <select id="edit-period" name="period_type" required
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            <option value="monthly">Bulanan</option>
                            <option value="semester">Per Semester</option>
                            <option value="once">Sekali Bayar</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Keterangan</label>
                        <textarea id="edit-desc" name="description" rows="2"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-edit-type').classList.add('hidden')"
                        class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2 rounded-lg transition-colors">Batal</button>
                    <button type="submit"
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2 rounded-lg transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Tambah Tarif --}}
    <div id="modal-add-rate" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-md p-6">
            <h3 class="text-white font-semibold mb-4">Tambah Tarif</h3>
            <form id="form-add-rate" method="POST" action="">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Tahun Ajaran *</label>
                        <select name="academic_year_id" required
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                                    {{ $year->name }} Sem {{ $year->semester }}{{ $year->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Kelas (kosong = semua)</label>
                            <select name="classroom_id"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                <option value="">Semua kelas</option>
                                @foreach($classrooms as $cls)
                                    <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Jurusan (kosong = semua)</label>
                            <select name="major_id"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                <option value="">Semua jurusan</option>
                                @foreach($majors as $major)
                                    <option value="{{ $major->id }}">{{ $major->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Nominal (Rp) *</label>
                        <input type="number" name="amount" required min="0" placeholder="500000"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-add-rate').classList.add('hidden')"
                        class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2 rounded-lg transition-colors">Batal</button>
                    <button type="submit"
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2 rounded-lg transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditType(id, name, code, category, period, desc) {
        document.getElementById('form-edit-type').action = `/bendahara/payment-types/${id}`;
        document.getElementById('edit-name').value    = name;
        document.getElementById('edit-code').value    = code;
        document.getElementById('edit-category').value = category;
        document.getElementById('edit-period').value  = period;
        document.getElementById('edit-desc').value    = desc;
        document.getElementById('modal-edit-type').classList.remove('hidden');
    }
    function openAddRate(typeId) {
        document.getElementById('form-add-rate').action = `/bendahara/payment-types/${typeId}/rates`;
        document.getElementById('modal-add-rate').classList.remove('hidden');
    }
    ['modal-add-type','modal-edit-type','modal-add-rate'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
    });
    </script>

</x-simans-layout>