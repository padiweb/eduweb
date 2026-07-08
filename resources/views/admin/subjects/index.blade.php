<x-simans-layout title="Mata Pelajaran">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Mata Pelajaran</h1>
            <p class="text-gray-400 text-sm mt-1">Kelola kelompok dan daftar mata pelajaran sekolah</p>
        </div>
        <a href="{{ route('admin.schedules.index') }}"
           class="flex items-center gap-2 text-sm text-gray-400 hover:text-white bg-gray-800 border border-white/10 px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
            </svg>
            Kelola Jadwal
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-300 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-900/30 border border-red-700/40 text-red-300 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tab: Kelompok | Mata Pelajaran --}}
    <div class="flex gap-1 mb-5 bg-gray-900 border border-white/5 rounded-xl p-1 w-fit" x-data="{ tab: 'subjects' }">
        <button @click="tab='subjects'"
                :class="tab==='subjects' ? 'bg-emerald-500 text-white' : 'text-gray-400 hover:text-white'"
                class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
            Mata Pelajaran
        </button>
        <button @click="tab='groups'"
                :class="tab==='groups' ? 'bg-emerald-500 text-white' : 'text-gray-400 hover:text-white'"
                class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
            Kelompok Mapel
        </button>

        {{-- TAB: Mata Pelajaran --}}
        <div x-show="tab==='subjects'" x-cloak class="hidden"></div>
        <div x-show="tab==='groups'" x-cloak class="hidden"></div>
    </div>

    <div x-data="{ tab: 'subjects' }">
        {{-- Tab switcher --}}
        <div class="flex gap-1 mb-5 bg-gray-900 border border-white/5 rounded-xl p-1 w-fit">
            <button @click="tab='subjects'"
                    :class="tab==='subjects' ? 'bg-emerald-500 text-white' : 'text-gray-400 hover:text-white'"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                Mata Pelajaran
            </button>
            <button @click="tab='groups'"
                    :class="tab==='groups' ? 'bg-emerald-500 text-white' : 'text-gray-400 hover:text-white'"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                Kelola Kelompok
            </button>
        </div>

        {{-- ── TAB MATA PELAJARAN ── --}}
        <div x-show="tab==='subjects'">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Form tambah mapel --}}
                <div>
                    <div class="bg-gray-900 border border-emerald-500/20 rounded-xl p-5">
                        <h2 class="text-sm font-semibold text-white mb-4">Tambah Mata Pelajaran</h2>
                        <form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Nama Mapel <span class="text-red-400">*</span></label>
                                <input type="text" name="name" required value="{{ old('name') }}"
                                       placeholder="cth: Matematika, Bahasa Indonesia"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Kode (opsional)</label>
                                <input type="text" name="code" value="{{ old('code') }}"
                                       placeholder="cth: MTK, BIN, TKJ"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Kelompok</label>
                                <select name="subject_group_id"
                                        class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                    <option value="">Tanpa kelompok</option>
                                    @foreach($groups->sortBy('sort_order') as $g)
                                        <option value="{{ $g->id }}" {{ old('subject_group_id') == $g->id ? 'selected' : '' }}>
                                            {{ $g->code ? '['.$g->code.'] ' : '' }}{{ $g->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($groups->isEmpty())
                                    <p class="text-xs text-amber-500 mt-1">Buat kelompok dulu di tab "Kelola Kelompok".</p>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Jurusan (opsional)</label>
                                <select name="major_id"
                                        class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                    <option value="">Semua jurusan</option>
                                    @foreach($majors as $m)
                                        <option value="{{ $m->id }}" {{ old('major_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-600 mt-1">Kosongkan jika mapel untuk semua jurusan.</p>
                            </div>
                            <button type="submit"
                                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                                Tambah Mapel
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Daftar mapel per kelompok --}}
                <div class="lg:col-span-2 space-y-4">
                    @if($subjects->isEmpty() && $groups->isEmpty())
                        <div class="bg-gray-900 border border-white/5 rounded-xl p-12 text-center">
                            <p class="text-gray-500 text-sm">Belum ada mata pelajaran.</p>
                            <p class="text-gray-600 text-xs mt-1">Buat kelompok dulu, lalu tambah mata pelajaran.</p>
                        </div>
                    @else
                        {{-- Mapel per kelompok --}}
                        @foreach($groups->sortBy('sort_order') as $group)
                            @php $mapels = $subjects->get($group->id, collect()); @endphp
                            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                                <div class="px-5 py-3 border-b border-white/5 bg-white/[0.02] flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-bold text-white uppercase tracking-wide">
                                            {{ $group->code ? '['.$group->code.']' : '' }} {{ $group->name }}
                                        </span>
                                        @if($group->description)
                                            <span class="text-xs text-gray-500 ml-2">— {{ $group->description }}</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-600">{{ $mapels->count() }} mapel</span>
                                </div>
                                @if($mapels->count() > 0)
                                    <div class="divide-y divide-white/5">
                                        @foreach($mapels->sortBy('name') as $subject)
                                            @include('admin.subjects._row', compact('subject', 'groups', 'majors'))
                                        @endforeach
                                    </div>
                                @else
                                    <p class="px-5 py-4 text-xs text-gray-600 italic">Belum ada mapel di kelompok ini.</p>
                                @endif
                            </div>
                        @endforeach

                        {{-- Mapel tanpa kelompok --}}
                        @if($subjects->has('') || $subjects->has(null))
                            @php $tanpaKelompok = $subjects->get('', collect())->merge($subjects->get(null, collect())); @endphp
                            @if($tanpaKelompok->count() > 0)
                                <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                                    <div class="px-5 py-3 border-b border-white/5 bg-white/[0.02]">
                                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanpa Kelompok</span>
                                    </div>
                                    <div class="divide-y divide-white/5">
                                        @foreach($tanpaKelompok->sortBy('name') as $subject)
                                            @include('admin.subjects._row', compact('subject', 'groups', 'majors'))
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- ── TAB KELOLA KELOMPOK ── --}}
        <div x-show="tab==='groups'">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Form tambah kelompok --}}
                <div>
                    <div class="bg-gray-900 border border-emerald-500/20 rounded-xl p-5">
                        <h2 class="text-sm font-semibold text-white mb-1">Tambah Kelompok Mapel</h2>
                        <p class="text-xs text-gray-500 mb-4">Sesuaikan dengan kurikulum sekolah.</p>
                        <form method="POST" action="{{ route('admin.subjects.groups.store') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Nama Kelompok <span class="text-red-400">*</span></label>
                                <input type="text" name="name" required value="{{ old('name') }}"
                                       placeholder="cth: Wajib Umum, Muatan Lokal"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Kode (opsional)</label>
                                <input type="text" name="code" value="{{ old('code') }}"
                                       placeholder="cth: A, B, C1, Mulok"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Keterangan (opsional)</label>
                                <input type="text" name="description" value="{{ old('description') }}"
                                       placeholder="cth: Mata pelajaran wajib nasional"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Urutan tampil</label>
                                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                <p class="text-xs text-gray-600 mt-1">Angka kecil tampil lebih atas.</p>
                            </div>
                            <button type="submit"
                                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                                Tambah Kelompok
                            </button>
                        </form>
                    </div>

                    <div class="mt-4 bg-gray-900 border border-white/5 rounded-xl p-4">
                        <p class="text-xs text-gray-500 mb-2 font-semibold">Contoh kelompok SMK:</p>
                        <div class="space-y-1 text-xs text-gray-600">
                            <p>[A] Muatan Nasional</p>
                            <p>[B] Muatan Kewilayahan</p>
                            <p>[C1] Dasar Bidang Keahlian</p>
                            <p>[C2] Dasar Program Keahlian</p>
                            <p>[C3] Kompetensi Keahlian</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-3 mb-1 font-semibold">Contoh kelompok SMP:</p>
                        <div class="space-y-1 text-xs text-gray-600">
                            <p>Mata Pelajaran Umum</p>
                            <p>Muatan Lokal</p>
                            <p>Pengembangan Diri</p>
                        </div>
                    </div>
                </div>

                {{-- Daftar kelompok --}}
                <div class="lg:col-span-2">
                    <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-white">Daftar Kelompok</h2>
                            <span class="text-xs text-gray-500">{{ $groups->count() }} kelompok</span>
                        </div>

                        @if($groups->isEmpty())
                            <div class="px-5 py-12 text-center">
                                <p class="text-gray-500 text-sm">Belum ada kelompok mapel.</p>
                                <p class="text-gray-600 text-xs mt-1">Tambahkan di form sebelah kiri.</p>
                            </div>
                        @else
                            <div class="divide-y divide-white/5">
                                @foreach($groups->sortBy('sort_order') as $group)
                                    <div class="px-5 py-4" x-data="{ editing: false }">
                                        {{-- Tampilan normal --}}
                                        <div class="flex items-center gap-4" x-show="!editing">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                                <span class="text-xs font-bold text-emerald-400">{{ $group->sort_order ?? '—' }}</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-white">
                                                    {{ $group->code ? '['.$group->code.'] ' : '' }}{{ $group->name }}
                                                </p>
                                                @if($group->description)
                                                    <p class="text-xs text-gray-500 mt-0.5">{{ $group->description }}</p>
                                                @endif
                                                <p class="text-xs text-gray-600 mt-0.5">{{ $group->subjects_count }} mata pelajaran</p>
                                            </div>
                                            <div class="flex items-center gap-1 flex-shrink-0">
                                                <button type="button" @click="editing=true"
                                                        class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-400 hover:text-white transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                                    </svg>
                                                </button>
                                                <form method="POST" action="{{ route('admin.subjects.groups.destroy', $group->id) }}"
                                                      onsubmit="return confirm('Hapus kelompok {{ addslashes($group->name) }}?')">
                                                    @csrf @method('DELETE')
                                                    <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-800 hover:bg-red-900/40 border border-white/10 hover:border-red-500/30 text-gray-400 hover:text-red-400 transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        {{-- Form edit inline --}}
                                        <form method="POST" action="{{ route('admin.subjects.groups.update', $group->id) }}"
                                              class="space-y-2" x-show="editing" x-cloak>
                                            @csrf @method('PUT')
                                            <div class="grid grid-cols-3 gap-2">
                                                <input type="text" name="name" value="{{ $group->name }}" required
                                                       placeholder="Nama kelompok"
                                                       class="col-span-2 bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                                <input type="text" name="code" value="{{ $group->code }}"
                                                       placeholder="Kode"
                                                       class="bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                            </div>
                                            <div class="grid grid-cols-3 gap-2">
                                                <input type="text" name="description" value="{{ $group->description }}"
                                                       placeholder="Keterangan"
                                                       class="col-span-2 bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                                <input type="number" name="sort_order" value="{{ $group->sort_order }}"
                                                       placeholder="Urutan" min="0"
                                                       class="bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                            </div>
                                            <div class="flex gap-2">
                                                <button type="submit"
                                                        class="flex-1 text-xs text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 hover:bg-emerald-500/20 py-1.5 rounded-lg transition-colors">
                                                    Simpan
                                                </button>
                                                <button type="button" @click="editing=false"
                                                        class="flex-1 text-xs text-gray-400 bg-gray-800 border border-white/10 py-1.5 rounded-lg transition-colors">
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
        </div>
    </div>

</x-simans-layout>
