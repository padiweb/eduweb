<x-simans-layout title="Kategori Pengeluaran">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Kategori Pengeluaran</h1>
            <p class="text-gray-500 text-sm mt-0.5">Setup kategori dan batas approval</p>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Kategori
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-600 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($categories->isEmpty())
            <div class="px-5 py-12 text-center"><p class="text-gray-500">Belum ada kategori.</p></div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Nama</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-4 py-3">Jenis</th>
                        <th class="text-center text-xs text-gray-500 font-medium px-4 py-3">Approval</th>
                        <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Batas Nominal</th>
                        <th class="text-right text-xs text-gray-500 font-medium px-4 py-3">Dipakai</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($categories as $cat)
                    <tr class="hover:bg-white/2 transition-colors">
                        <td class="px-4 py-3">
                            <span class="text-gray-900 font-medium">{{ $cat->name }}</span>
                            @if($cat->code) <span class="text-xs text-gray-500 ml-2">{{ $cat->code }}</span> @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $cat->getTypeLabel() }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($cat->requires_approval)
                                <span class="text-xs bg-amber-50 text-amber-600 border border-amber-200 px-2 py-0.5 rounded-full">Ya</span>
                            @else
                                <span class="text-xs text-gray-500">Tidak</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">
                            @if($cat->requires_approval)
                                @if($cat->approval_threshold > 0)
                                    ≥ Rp {{ number_format($cat->approval_threshold, 0, ',', '.') }}
                                @else
                                    Semua nominal
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $cat->expenses_count }}x</td>
                        <td class="px-4 py-3 text-right">
                            <button onclick="openEdit({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ $cat->code }}', '{{ $cat->type }}', {{ $cat->requires_approval ? 1:0 }}, {{ $cat->approval_threshold }})"
                                class="text-xs text-gray-500 hover:text-blue-600">Edit</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- MODAL Tambah --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4" x-data="{ req: false }">
        <div class="bg-white border border-gray-200 rounded-xl w-full max-w-md p-6">
            <h3 class="text-gray-900 font-semibold mb-4">Tambah Kategori</h3>
            <form method="POST" action="{{ route('bendahara.expenses.categories.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Nama *</label>
                        <input type="text" name="name" required placeholder="Penggajian, ATK, Kegiatan, dll"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Kode</label>
                            <input type="text" name="code" placeholder="GAJI"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Jenis *</label>
                            <select name="type" required class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                                <option value="payroll">Penggajian</option>
                                <option value="activity">Kegiatan</option>
                                <option value="operational">Operasional</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="requires_approval" value="1" x-model="req" class="text-blue-600">
                            <span class="text-sm text-gray-900">Membutuhkan approval kepala sekolah</span>
                        </label>
                    </div>
                    <div x-show="req" x-cloak>
                        <label class="text-xs text-gray-500 mb-1 block">Batas Nominal (Rp) — kosong = semua nominal</label>
                        <input type="number" name="approval_threshold" min="0" placeholder="0 = semua nominal butuh approval"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        <p class="text-xs text-gray-500 mt-1">Isi 0 atau kosong = semua pengeluaran di kategori ini butuh approval</p>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="flex-1 bg-white hover:bg-gray-50 text-gray-600 text-sm font-medium py-2 rounded-lg">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL Edit --}}
    <div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4" x-data="{ req: false }">
        <div class="bg-white border border-gray-200 rounded-xl w-full max-w-md p-6">
            <h3 class="text-gray-900 font-semibold mb-4">Edit Kategori</h3>
            <form id="form-edit" method="POST" action="">
                @csrf @method('PUT')
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Nama *</label>
                        <input type="text" id="edit-name" name="name" required
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Kode</label>
                            <input type="text" id="edit-code" name="code"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Jenis *</label>
                            <select id="edit-type" name="type" required class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                                <option value="payroll">Penggajian</option>
                                <option value="activity">Kegiatan</option>
                                <option value="operational">Operasional</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="edit-req" name="requires_approval" value="1" x-model="req" class="text-blue-600">
                            <span class="text-sm text-gray-900">Membutuhkan approval</span>
                        </label>
                    </div>
                    <div x-show="req" x-cloak>
                        <label class="text-xs text-gray-500 mb-1 block">Batas Nominal (Rp)</label>
                        <input type="number" id="edit-threshold" name="approval_threshold" min="0"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="flex-1 bg-white hover:bg-gray-50 text-gray-600 text-sm font-medium py-2 rounded-lg">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEdit(id, name, code, type, req, threshold) {
        document.getElementById('form-edit').action = `/bendahara/expense-categories/${id}`;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-code').value = code;
        document.getElementById('edit-type').value = type;
        document.getElementById('edit-req').checked = req == 1;
        document.getElementById('edit-threshold').value = threshold;
        document.getElementById('modal-edit').classList.remove('hidden');
    }
    ['modal-add','modal-edit'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
    });
    </script>
</x-simans-layout>
