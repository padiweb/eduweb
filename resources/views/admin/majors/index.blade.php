<x-simans-layout title="Jurusan / Program Keahlian">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Jurusan / Program Keahlian</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola jurusan atau program keahlian sekolah</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form tambah --}}
        <div>
            <div class="bg-white border border-blue-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Tambah Jurusan</h2>
                <form method="POST" action="{{ route('admin.majors.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Nama Jurusan <span class="text-red-600">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                               placeholder="cth: Teknik Komputer & Jaringan"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Kode (opsional)</label>
                        <input type="text" name="code" value="{{ old('code') }}"
                               placeholder="cth: TKJ, RPL, TKR"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Tambah Jurusan
                    </button>
                </form>
            </div>
            <p class="text-xs text-gray-500 mt-3 px-1">Jurusan bersifat opsional. Sekolah tanpa jurusan (SMP/SD) bisa lewati bagian ini.</p>
        </div>

        {{-- Daftar jurusan --}}
        <div class="lg:col-span-2">
            <div class="tbl-card">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">Semua Jurusan</h2>
                    <span class="text-xs text-gray-500">{{ $majors->count() }} jurusan</span>
                </div>

                @if($majors->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($majors as $major)
                            <div class="flex items-center gap-4 px-5 py-4" x-data="{ editing: false }">
                                <div class="flex-1 min-w-0" x-show="!editing">
                                    <p class="text-sm font-semibold text-gray-900">{{ $major->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $major->code ? 'Kode: '.$major->code.' &middot; ' : '' }}{{ $major->classrooms_count }} kelas
                                    </p>
                                </div>

                                {{-- Form edit inline --}}
                                <form method="POST" action="{{ route('admin.majors.update', $major->id) }}"
                                      class="flex-1 flex gap-2" x-show="editing" x-cloak>
                                    @csrf @method('PUT')
                                    <input type="text" name="name" value="{{ $major->name }}" required
                                           class="flex-1 bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                    <input type="text" name="code" value="{{ $major->code }}"
                                           placeholder="Kode"
                                           class="w-20 bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-700 px-2 py-1.5 bg-blue-50 border border-blue-200 rounded-lg transition-colors">
                                        Simpan
                                    </button>
                                    <button type="button" @click="editing=false" class="text-xs text-gray-500 hover:text-blue-600 px-2 py-1.5 bg-white border border-gray-200 rounded-lg transition-colors">
                                        Batal
                                    </button>
                                </form>

                                <div class="flex items-center gap-1 flex-shrink-0" x-show="!editing">
                                    <button type="button" @click="editing=true"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.majors.destroy', $major->id) }}"
                                          onsubmit="return confirm('Hapus jurusan {{ addslashes($major->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-white hover:bg-red-50 border border-gray-200 hover:border-red-200 text-white hover:text-red-600 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-12 text-center">
                        <p class="text-gray-500 text-sm">Belum ada jurusan.</p>
                        <p class="text-gray-500 text-xs mt-1">Tambahkan di form sebelah kiri, atau lewati jika sekolah tidak pakai jurusan.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-simans-layout>
