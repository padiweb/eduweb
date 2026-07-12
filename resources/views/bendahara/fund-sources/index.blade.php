<x-simans-layout title="Sumber Dana">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-white">Sumber Dana</h1>
            <p class="text-gray-400 text-sm mt-0.5">BOS, BOSDA, kas siswa, dan sumber lainnya</p>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Sumber Dana
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
    @endif

    {{-- Info kenapa tidak ada hapus --}}
    <div class="bg-blue-500/5 border border-blue-500/15 rounded-xl px-4 py-3 mb-5 text-xs text-blue-400">
        <strong>Catatan:</strong> Sumber dana tidak dapat dihapus karena menyimpan history keuangan. Gunakan tombol <strong>Nonaktifkan</strong> jika sumber dana tidak lagi digunakan.
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($sources as $source)
        <div class="bg-gray-900 border {{ $source->is_active ? 'border-white/5' : 'border-white/3 opacity-60' }} rounded-xl p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-white font-semibold">{{ $source->name }}</span>
                        @if($source->code)
                            <span class="text-xs text-gray-500 bg-gray-800 px-2 py-0.5 rounded">{{ $source->code }}</span>
                        @endif
                        @if(!$source->is_active)
                            <span class="text-xs bg-red-500/10 text-red-400 border border-red-500/20 px-2 py-0.5 rounded-full">Nonaktif</span>
                        @endif
                    </div>
                    <p class="text-xs mt-1.5">
                        <span class="bg-{{ $source->getTypeBadgeColor() }}-500/10 text-{{ $source->getTypeBadgeColor() }}-400 border border-{{ $source->getTypeBadgeColor() }}-500/20 px-2 py-0.5 rounded-full">
                            {{ $source->getTypeLabel() }}
                        </span>
                    </p>
                </div>
                <div class="flex flex-col items-end gap-1.5">
                    <button onclick="openEdit({{ $source->id }}, '{{ addslashes($source->name) }}', '{{ $source->code }}', '{{ $source->type }}', '{{ addslashes($source->description ?? '') }}')"
                        class="text-xs text-gray-500 hover:text-white transition-colors">Edit</button>
                    <form method="POST" action="{{ route('bendahara.fund-sources.toggle', $source) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="text-xs {{ $source->is_active ? 'text-amber-500 hover:text-amber-400' : 'text-green-500 hover:text-green-400' }} transition-colors">
                            {{ $source->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="space-y-1.5 text-sm border-t border-white/5 pt-4 mb-4">
                <div class="flex justify-between">
                    <span class="text-gray-400">Total pemasukan</span>
                    <span class="text-green-400 font-medium">Rp {{ number_format($source->total_income, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Total pengeluaran</span>
                    <span class="text-red-400 font-medium">Rp {{ number_format($source->total_expense, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-white/5 pt-1.5 font-semibold">
                    <span class="text-gray-300">Saldo</span>
                    <span class="{{ $source->balance >= 0 ? 'text-white' : 'text-red-400' }}">
                        Rp {{ number_format(abs($source->balance), 0, ',', '.') }}
                        {{ $source->balance < 0 ? '(Defisit)' : '' }}
                    </span>
                </div>
            </div>

            <a href="{{ route('bendahara.fund-sources.incomes', $source) }}"
                class="flex items-center justify-center gap-1.5 w-full text-xs text-purple-400 hover:text-purple-300 border border-purple-500/20 hover:border-purple-500/40 rounded-lg py-2 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                </svg>
                Lihat {{ $source->incomes_count }} Pemasukan
            </a>

            @if($source->description)
                <p class="text-xs text-gray-600 mt-3">{{ $source->description }}</p>
            @endif
        </div>
        @empty
            <div class="col-span-3 bg-gray-900 border border-white/5 rounded-xl px-5 py-12 text-center">
                <p class="text-gray-500">Belum ada sumber dana.</p>
                <p class="text-gray-600 text-xs mt-1">Klik "Tambah Sumber Dana" untuk mulai — contoh: BOS, BOSDA, Kas Siswa.</p>
            </div>
        @endforelse
    </div>

    {{-- MODAL: Tambah --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-md p-6">
            <h3 class="text-white font-semibold mb-4">Tambah Sumber Dana</h3>
            <form method="POST" action="{{ route('bendahara.fund-sources.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Nama *</label>
                        <input type="text" name="name" required placeholder="BOS, BOSDA, Kas Siswa, dll"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Kode (opsional)</label>
                            <input type="text" name="code" placeholder="BOS"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Jenis *</label>
                            <select name="type" required class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                <option value="siswa">Kas Siswa</option>
                                <option value="bos">Dana BOS</option>
                                <option value="bosda">Dana BOSDA</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Keterangan</label>
                        <textarea name="description" rows="2" placeholder="Opsional..."
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2 rounded-lg">Batal</button>
                    <button type="submit"
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2 rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Edit --}}
    <div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-md p-6">
            <h3 class="text-white font-semibold mb-4">Edit Sumber Dana</h3>
            <form id="form-edit" method="POST" action="">
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
                            <label class="text-xs text-gray-400 mb-1 block">Jenis *</label>
                            <select id="edit-type" name="type" required class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                <option value="siswa">Kas Siswa</option>
                                <option value="bos">Dana BOS</option>
                                <option value="bosda">Dana BOSDA</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Keterangan</label>
                        <textarea id="edit-desc" name="description" rows="2"
                            class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2 rounded-lg">Batal</button>
                    <button type="submit"
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2 rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEdit(id, name, code, type, desc) {
        document.getElementById('form-edit').action = `/bendahara/fund-sources/${id}`;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-code').value = code;
        document.getElementById('edit-type').value = type;
        document.getElementById('edit-desc').value = desc;
        document.getElementById('modal-edit').classList.remove('hidden');
    }
    ['modal-add','modal-edit'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
    });
    </script>

</x-simans-layout>
