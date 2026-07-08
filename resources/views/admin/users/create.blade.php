<x-simans-layout title="{{ isset($user) ? 'Edit User' : 'Tambah User' }}">

    @php
        $isEdit    = isset($user);
        $role      = $isEdit ? $user->role : ($role ?? 'siswa');
        $isSiswa   = $role === 'siswa';
        $isGuru    = in_array($role, ['guru', 'wali_kelas']);
        $detail    = $isEdit ? ($isSiswa ? $user->studentDetail : $user->teacherDetail) : null;
    @endphp

    <div class="mb-6">
        <a href="{{ route('admin.users.index', ['tab' => $role]) }}"
           class="flex items-center gap-1 text-gray-400 hover:text-white text-sm mb-2 transition-colors w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-white">
            {{ $isEdit ? 'Edit' : 'Tambah' }} {{ ucfirst(str_replace('_',' ',$role)) }}
        </h1>
    </div>

    @if($errors->any())
        <div class="mb-5 bg-red-900/30 border border-red-700/40 text-red-300 px-4 py-3 rounded-xl text-sm">
            <ul class="space-y-1">
                @foreach($errors->all() as $err)
                    <li>&bull; {{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $isEdit ? route('admin.users.update', $user->id) : route('admin.users.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <input type="hidden" name="role" value="{{ $role }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Kolom Kiri: Foto + Status --}}
            <div class="space-y-4">
                {{-- Foto --}}
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-4">Foto</h2>
                    @if($detail?->photo_path)
                        <div class="mb-3 flex justify-center">
                            <div style="width:96px;height:96px;border-radius:50%;overflow:hidden;flex-shrink:0;border:2px solid rgba(255,255,255,0.1);">
                                <img src="{{ asset('storage/'.$detail->photo_path) }}"
                                     alt="Foto"
                                     style="width:100%;height:100%;object-fit:cover;display:block;">
                            </div>
                        </div>
                    @endif
                    <input type="file" name="photo" accept="image/*"
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2 text-sm focus:outline-none transition-colors file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-gray-700 file:text-gray-300">
                    <p class="text-xs text-gray-600 mt-1">JPG/PNG, maks 2MB.</p>
                </div>

                {{-- Status akun --}}
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-4">Status Akun</h2>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               {{ (old('is_active', $isEdit ? $user->is_active : true)) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-600 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0">
                        <span class="text-sm text-white">Akun Aktif</span>
                    </label>
                    <p class="text-xs text-gray-600 mt-1">Nonaktif = tidak bisa login.</p>
                </div>

                {{-- Kelas (siswa) --}}
                @if($isSiswa)
                    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                        <h2 class="text-sm font-semibold text-white mb-4">Kelas Aktif</h2>
                        <select name="classroom_id"
                                class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <option value="">-- Belum ada kelas --</option>
                            @foreach($classrooms as $c)
                                <option value="{{ $c->id }}"
                                    {{ old('classroom_id', $isEdit ? $user->classrooms->first()?->id : '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Jabatan (guru) --}}
                @if($isGuru)
                    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                        <h2 class="text-sm font-semibold text-white mb-4">Jabatan</h2>
                        @if($positions->count() > 0)
                            <div class="space-y-2">
                                @foreach($positions as $pos)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="position_ids[]" value="{{ $pos->id }}"
                                               {{ $isEdit && $user->positions->contains($pos->id) ? 'checked' : '' }}
                                               class="w-4 h-4 rounded border-gray-600 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0">
                                        <span class="text-sm text-white">{{ $pos->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-500">Belum ada jabatan. Buat di
                                <a href="{{ route('admin.users.positions') }}" class="text-emerald-400 hover:underline">Kelola Jabatan</a>.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Kolom Kanan: Form data --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Data akun --}}
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-4">Data Akun</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs text-gray-400 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                            <input type="text" name="name" required
                                   value="{{ old('name', $user->name ?? '') }}"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        </div>
                        @if($isSiswa)
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">NIS</label>
                                <input type="text" name="nis" value="{{ old('nis', $user->nis ?? '') }}"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                       placeholder="Nomor Induk Siswa">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">NISN</label>
                                <input type="text" name="nisn" value="{{ old('nisn', $user->nisn ?? '') }}"
                                       maxlength="10"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                       placeholder="10 digit">
                            </div>
                        @else
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">NIP</label>
                                <input type="text" name="nip" value="{{ old('nip', $user->nip ?? '') }}"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                       placeholder="Untuk ASN/PPPK">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">NIY</label>
                                <input type="text" name="niy" value="{{ old('niy', $user->niy ?? '') }}"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                       placeholder="Untuk honorer/GTY">
                            </div>
                        @endif
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Username Login</label>
                            <input type="text" name="username" value="{{ old('username', $user->username ?? '') }}"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                   placeholder="Opsional, bisa pakai NIS/NIP">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">
                                No. HP / WhatsApp
                            </label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                   placeholder="08xxxxxxxxxx">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">
                                Password {{ $isEdit ? '(kosongkan jika tidak diubah)' : '' }}
                                @if(! $isEdit) <span class="text-red-400">*</span> @endif
                            </label>
                            <input type="password" name="password"
                                   {{ ! $isEdit ? 'required' : '' }}
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                   placeholder="Min. 6 karakter">
                        </div>
                    </div>
                </div>

                {{-- Data Pribadi --}}
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-4">Data Pribadi</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Tempat Lahir</label>
                            <input type="text" name="birth_place" value="{{ old('birth_place', $detail?->birth_place ?? '') }}"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Tanggal Lahir</label>
                            <input type="date" name="birth_date"
                                   value="{{ old('birth_date', $detail?->birth_date?->format('Y-m-d') ?? '') }}"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Jenis Kelamin</label>
                            <select name="gender"
                                    class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                <option value="">-- Pilih --</option>
                                <option value="L" {{ old('gender', $detail?->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender', $detail?->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Agama</label>
                            <select name="religion"
                                    class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                <option value="">-- Pilih --</option>
                                @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $agama)
                                    <option value="{{ $agama }}" {{ old('religion', $detail?->religion) === $agama ? 'selected' : '' }}>{{ $agama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs text-gray-400 mb-1.5">Alamat</label>
                            <textarea name="address" rows="2"
                                      class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 resize-none transition-colors">{{ old('address', $detail?->address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Data khusus siswa --}}
                @if($isSiswa)
                    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                        <h2 class="text-sm font-semibold text-white mb-4">Data Kependudukan</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">NIK Siswa</label>
                                <input type="text" name="nik" maxlength="16"
                                       value="{{ old('nik', $detail?->nik ?? '') }}"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                       placeholder="16 digit">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Nomor KK</label>
                                <input type="text" name="no_kk" maxlength="16"
                                       value="{{ old('no_kk', $detail?->no_kk ?? '') }}"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                       placeholder="16 digit">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">WhatsApp Siswa</label>
                                <input type="text" name="whatsapp"
                                       value="{{ old('whatsapp', $detail?->whatsapp ?? '') }}"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                       placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                        <h2 class="text-sm font-semibold text-white mb-4">Data Orang Tua / Wali</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Nama Ayah</label>
                                <input type="text" name="father_name"
                                       value="{{ old('father_name', $detail?->father_name ?? '') }}"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Nama Ibu</label>
                                <input type="text" name="mother_name"
                                       value="{{ old('mother_name', $detail?->mother_name ?? '') }}"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-gray-400 mb-1.5">WhatsApp Orang Tua</label>
                                <input type="text" name="parent_whatsapp"
                                       value="{{ old('parent_whatsapp', $detail?->parent_whatsapp ?? '') }}"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                       placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Data khusus guru --}}
                @if($isGuru)
                    <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                        <h2 class="text-sm font-semibold text-white mb-4">Data Kepegawaian</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Status Kepegawaian</label>
                                <select name="employment_status"
                                        class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                    <option value="">-- Pilih --</option>
                                    @foreach(['ASN','PPPK','Kontrak','Honor','GTY'] as $st)
                                        <option value="{{ $st }}" {{ old('employment_status', $detail?->employment_status) === $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Status Perkawinan</label>
                                <select name="marital_status"
                                        class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                    <option value="">-- Pilih --</option>
                                    @foreach(['Belum Kawin','Kawin','Cerai Hidup','Cerai Mati'] as $ms)
                                        <option value="{{ $ms }}" {{ old('marital_status', $detail?->marital_status) === $ms ? 'selected' : '' }}>{{ $ms }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Jumlah Anak</label>
                                <input type="number" name="children_count" min="0"
                                       value="{{ old('children_count', $detail?->children_count ?? 0) }}"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Tombol simpan --}}
                <div class="flex gap-3 justify-end">
                    <a href="{{ route('admin.users.index', ['tab' => $role]) }}"
                       class="px-6 py-2.5 text-sm font-medium rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 border border-white/10 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 text-sm font-semibold rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white transition-colors">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah User' }}
                    </button>
                </div>
            </div>
        </div>
    </form>

</x-simans-layout>
