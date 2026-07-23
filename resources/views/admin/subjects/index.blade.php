<x-simans-layout title="Mata Pelajaran">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mata Pelajaran</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola kelompok dan daftar mata pelajaran sekolah</p>
        </div>
        <a href="{{ route('admin.schedules.index') }}"
           class="flex items-center gap-2 text-sm text-gray-500 hover:text-blue-600 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
            </svg>
            Kelola Jadwal
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-900/30 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    @php $activeTab = request('tab', 'subjects'); @endphp
    <div class="flex gap-1 mb-6 bg-white border border-gray-200 rounded-xl p-1 w-fit">
        <a href="{{ route('admin.subjects.index', ['tab' => 'subjects']) }}"
           class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $activeTab === 'subjects' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-white' }}">
            Mata Pelajaran
        </a>
        <a href="{{ route('admin.subjects.index', ['tab' => 'groups']) }}"
           class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $activeTab === 'groups' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-white' }}">
            Kelola Kelompok
        </a>
    </div>

    @if($activeTab === 'subjects')
    {{-- TAB MATA PELAJARAN --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div>
            <div class="bg-white border border-blue-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Tambah Mata Pelajaran</h2>
                <form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Nama Mapel <span class="text-red-600">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                               placeholder="cth: Matematika, Bahasa Indonesia"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Kode (opsional)</label>
                        <input type="text" name="code" value="{{ old('code') }}"
                               placeholder="cth: MTK, BIN, PKN"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Kelompok</label>
                        <select name="subject_group_id"
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">Tanpa kelompok</option>
                            @foreach($groups->sortBy('sort_order') as $g)
                                <option value="{{ $g->id }}" {{ old('subject_group_id') == $g->id ? 'selected' : '' }}>
                                    {{ $g->code ? '['.$g->code.'] ' : '' }}{{ $g->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($groups->isEmpty())
                            <p class="text-xs text-amber-500 mt-1">
                                <a href="{{ route('admin.subjects.index', ['tab'=>'groups']) }}" class="underline">Buat kelompok dulu.</a>
                            </p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Jurusan (opsional)</label>
                        <select name="major_id"
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">Semua jurusan</option>
                            @foreach($majors as $m)
                                <option value="{{ $m->id }}" {{ old('major_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika mapel untuk semua jurusan.</p>
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Tambah Mapel
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-4">
            @if($subjects->flatten()->isEmpty())
                <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
                    <p class="text-gray-500 text-sm">Belum ada mata pelajaran.</p>
                </div>
            @else
                @foreach($groups->sortBy('sort_order') as $group)
                    @php $mapels = $subjects->get($group->id, collect()); @endphp
                    @if($mapels->count() > 0)
                        <div class="tbl-card">
                            <div class="px-5 py-3 border-b border-gray-200 bg-white/[0.02] flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-900 uppercase tracking-wide">
                                    {{ $group->code ? '['.$group->code.'] ' : '' }}{{ $group->name }}
                                </span>
                                <span class="text-xs text-gray-500">{{ $mapels->count() }} mapel</span>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @foreach($mapels->sortBy('name') as $subject)
                                    @include('admin.subjects._row', compact('subject','groups','majors'))
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                @php
                    $tanpaKelompok = collect();
                    foreach([null, '', 0] as $k) {
                        if ($subjects->has($k)) $tanpaKelompok = $tanpaKelompok->merge($subjects->get($k));
                    }
                    $tanpaKelompok = $tanpaKelompok->unique('id');
                @endphp
                @if($tanpaKelompok->count() > 0)
                    <div class="tbl-card">
                        <div class="px-5 py-3 border-b border-gray-200 bg-white/[0.02]">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanpa Kelompok</span>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @foreach($tanpaKelompok->sortBy('name') as $subject)
                                @include('admin.subjects._row', compact('subject','groups','majors'))
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
    @endif

    @if($activeTab === 'groups')
    {{-- TAB KELOLA KELOMPOK --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div>
            <div class="bg-white border border-blue-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-1">Tambah Kelompok Mapel</h2>
                <p class="text-xs text-gray-500 mb-4">Sesuaikan dengan kurikulum sekolah.</p>
                <form method="POST" action="{{ route('admin.subjects.groups.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Nama Kelompok <span class="text-red-600">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                               placeholder="cth: Wajib Umum, Muatan Lokal"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Kode (opsional)</label>
                        <input type="text" name="code" value="{{ old('code') }}"
                               placeholder="cth: A, B, C1, Mulok"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Keterangan (opsional)</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                               placeholder="cth: Mata pelajaran wajib nasional"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Urutan tampil</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        <p class="text-xs text-gray-500 mt-1">Angka kecil tampil lebih atas.</p>
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Tambah Kelompok
                    </button>
                </form>
            </div>

            <div class="mt-4 bg-white border border-gray-200 rounded-xl p-4 space-y-3">
                <div>
                    <p class="text-xs font-semibold text-gray-500 mb-1">Contoh SMK:</p>
                    <div class="space-y-0.5 text-xs text-gray-500">
                        <p>[A] Muatan Nasional</p>
                        <p>[B] Muatan Kewilayahan</p>
                        <p>[C1] Dasar Bidang Keahlian</p>
                        <p>[C2] Dasar Program Keahlian</p>
                        <p>[C3] Kompetensi Keahlian</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 mb-1">Contoh SMP:</p>
                    <div class="space-y-0.5 text-xs text-gray-500">
                        <p>Mata Pelajaran Umum</p>
                        <p>Muatan Lokal</p>
                        <p>Pengembangan Diri</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="tbl-card">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">Daftar Kelompok</h2>
                    <span class="text-xs text-gray-500">{{ $groups->count() }} kelompok</span>
                </div>

                @if($groups->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <p class="text-gray-500 text-sm">Belum ada kelompok mapel.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($groups->sortBy('sort_order') as $group)
                            <div class="px-5 py-4" x-data="{ editing: false }">
                                <div class="flex items-center gap-4" x-show="!editing">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-blue-600">{{ $group->sort_order }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $group->code ? '['.$group->code.'] ' : '' }}{{ $group->name }}
                                        </p>
                                        @if($group->description)
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $group->description }}</p>
                                        @endif
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $group->subjects_count }} mata pelajaran</p>
                                    </div>
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        <button type="button" @click="editing=true"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.subjects.groups.destroy', $group->id) }}"
                                              onsubmit="return confirm('Hapus kelompok {{ addslashes($group->name) }}?')">
                                            @csrf @method('DELETE')
                                            <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-white hover:bg-red-900/40 border border-gray-200 hover:border-red-200 text-white hover:text-red-600 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('admin.subjects.groups.update', $group->id) }}"
                                      class="space-y-2" x-show="editing" x-cloak>
                                    @csrf @method('PUT')
                                    <div class="grid grid-cols-3 gap-2">
                                        <input type="text" name="name" value="{{ $group->name }}" required
                                               placeholder="Nama kelompok"
                                               class="col-span-2 bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                        <input type="text" name="code" value="{{ $group->code }}" placeholder="Kode"
                                               class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <input type="text" name="description" value="{{ $group->description }}"
                                               placeholder="Keterangan"
                                               class="col-span-2 bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                        <input type="number" name="sort_order" value="{{ $group->sort_order }}"
                                               placeholder="Urutan" min="0"
                                               class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit"
                                                class="flex-1 text-xs text-blue-600 bg-blue-50 border border-blue-200 hover:bg-blue-50 py-1.5 rounded-lg transition-colors">
                                            Simpan
                                        </button>
                                        <button type="button" @click="editing=false"
                                                class="flex-1 text-xs text-gray-500 bg-white border border-gray-200 py-1.5 rounded-lg transition-colors">
                                            Batal
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

</x-simans-layout>
