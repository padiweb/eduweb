<x-simans-layout title="Kelola Jabatan">

    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}"
           class="flex items-center gap-1 text-gray-500 hover:text-gray-900 text-sm mb-2 transition-colors w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Kelola Jabatan</h1>
        <p class="text-gray-500 text-sm mt-1">Buat jabatan yang bisa diassign ke guru (bisa rangkap jabatan)</p>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form tambah jabatan --}}
        <div>
            <div class="bg-white border border-blue-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Tambah Jabatan</h2>
                <form method="POST" action="{{ route('admin.users.positions.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Nama Jabatan <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required
                               placeholder="cth: Kepala Sekolah, Waka Kurikulum, BP/BK"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-gray-900 text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Tambah Jabatan
                    </button>
                </form>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-400">Contoh jabatan umum:</p>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach(['Kepala Sekolah','Waka Kurikulum','Waka Kesiswaan','Waka Sarpras','Waka Humas','Wali Kelas','BP/BK','Kepala Lab','Kepala Perpustakaan','Bendahara'] as $contoh)
                            <span class="text-xs text-gray-400 bg-white border border-gray-200 px-2 py-1 rounded-lg">{{ $contoh }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar jabatan --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">Daftar Jabatan</h2>
                    <span class="text-xs text-gray-400">{{ $positions->count() }} jabatan</span>
                </div>
                @if($positions->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($positions as $pos)
                            <div class="flex items-center gap-4 px-5 py-3.5">
                                <div class="w-8 h-8 rounded-full bg-blue-600/10 border border-blue-200 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $pos->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $pos->teachers_count }} guru memegang jabatan ini</p>
                                </div>
                                <form method="POST" action="{{ route('admin.users.positions.destroy', $pos->id) }}"
                                      onsubmit="return confirm('Hapus jabatan {{ addslashes($pos->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white hover:bg-red-900/40 border border-gray-200 hover:border-red-500/30 text-gray-500 hover:text-red-400 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-12 text-center">
                        <p class="text-gray-400 text-sm">Belum ada jabatan. Tambahkan di form sebelah kiri.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-simans-layout>
