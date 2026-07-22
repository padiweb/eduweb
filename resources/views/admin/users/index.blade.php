<x-simans-layout title="Manajemen User">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Manajemen User</h1>
            <p class="text-gray-400 text-sm mt-1">Kelola akun siswa, guru, dan staff sekolah</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.positions') }}"
               class="flex items-center gap-2 text-sm text-gray-400 hover:text-white bg-gray-800 border border-white/10 px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                </svg>
                Kelola Jabatan
            </a>
            <a href="{{ route('admin.users.import', ['role' => $tab]) }}"
               class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-300 text-sm px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                Import CSV
            </a>
            <a href="{{ route('admin.users.create', ['role' => $tab]) }}"
               class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Tambah
            </a>
        </div>
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

    {{-- Tab role --}}
    <div class="flex flex-wrap gap-1 mb-4 bg-gray-900 border border-white/5 rounded-xl p-1 w-fit">
        @foreach(['siswa'=>'Siswa','guru'=>'Guru','wali_kelas'=>'Wali Kelas','kesiswaan'=>'Kesiswaan','admin'=>'Admin','bendahara'=>'Bendahara','kepala_sekolah'=>'Kepala Sekolah'] as $role => $label)
            <a href="{{ route('admin.users.index', ['tab' => $role]) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $tab === $role ? 'bg-emerald-500 text-white' : 'text-gray-400 hover:text-white' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Search & Filter --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-2 mb-4">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIS, email..."
               class="flex-1 min-w-48 bg-gray-900 border border-white/10 text-white rounded-xl px-4 py-2 text-sm placeholder-gray-600 focus:outline-none focus:border-emerald-500/50">
        @if($tab === 'siswa')
        <select name="kelas_id" class="bg-gray-900 border border-white/10 text-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none">
            <option value="">Semua Kelas</option>
            @foreach($classrooms as $cls)
                <option value="{{ $cls->id }}" {{ $kelasId == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
            @endforeach
        </select>
        @endif
        <select name="status" class="bg-gray-900 border border-white/10 text-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none">
            <option value="">Semua Status</option>
            <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl transition-colors">Cari</button>
        @if($search || $kelasId || $status)
            <a href="{{ route('admin.users.index', ['tab' => $tab]) }}" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm rounded-xl border border-white/10 transition-colors">Reset</a>
        @endif
    </form>

    <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-white">{{ ucfirst(str_replace('_',' ',$tab)) }}</h2>
            <span class="text-xs text-gray-500">{{ $users->total() }} user</span>
        </div>

        @if($users->count() > 0)
            <div class="divide-y divide-white/5">
                @foreach($users as $user)
                    @php
                        $detail    = $user->studentDetail ?? $user->teacherDetail;
                        $classroom = $user->classrooms->first();
                    @endphp
                    <div class="flex items-center gap-3 px-5 py-4 hover:bg-white/[0.02] transition-colors">

                        {{-- Avatar --}}
                        <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-800 flex-shrink-0 ring-2 ring-white/5">
                            <img src="{{ $user->avatarUrl }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-white truncate">{{ $user->name }}</p>
                                @if(! $user->is_active)
                                    <span class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 px-1.5 py-0.5 rounded-full">Nonaktif</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if($user->role === 'siswa')
                                    NIS: {{ $user->nis ?? '-' }} &middot; NISN: {{ $user->nisn ?? '-' }}
                                    @if($classroom) &middot; {{ $classroom->name }} @endif
                                @else
                                    {{ $user->nip ?? ($user->niy ?? 'Tanpa NIP') }}
                                    @if($user->positions->count() > 0)
                                        &middot; {{ $user->positions->pluck('name')->join(', ') }}
                                    @endif
                                @endif
                            </p>
                            @if($detail?->birth_date || $user->phone)
                                <p class="text-xs text-gray-600 mt-0.5">
                                    {{ $detail?->gender === 'L' ? 'L' : ($detail?->gender === 'P' ? 'P' : '') }}
                                    @if($detail?->birth_date) &middot; {{ $detail->age }} thn @endif
                                    @if($user->phone) &middot; {{ $user->phone }} @endif
                                </p>
                            @endif
                        </div>

                        {{-- Login --}}
                        <div class="text-right flex-shrink-0 hidden md:block">
                            <p class="text-xs text-gray-500">{{ $user->username ?? $user->nis ?? $user->nip ?? $user->niy ?? '-' }}</p>
                            <p class="text-xs text-gray-600 truncate max-w-[140px]">{{ $user->email ?? '' }}</p>
                        </div>

                        {{-- Aksi --}}
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <a href="{{ route('admin.users.edit', $user->id) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-400 hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}">
                                @csrf @method('PATCH')
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-800 hover:bg-gray-700 border border-white/10 transition-colors {{ $user->is_active ? 'text-amber-400' : 'text-emerald-400' }}"
                                        title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        @if($user->is_active)
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        @endif
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                  onsubmit="return confirm('Hapus {{ addslashes($user->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-800 hover:bg-red-900/40 border border-white/10 hover:border-red-500/30 text-gray-400 hover:text-red-400 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="px-5 py-4 border-t border-white/5">{{ $users->links() }}</div>
        @else
            <div class="px-5 py-12 text-center">
                <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
                <p class="text-gray-500 text-sm">Belum ada {{ str_replace('_',' ',$tab) }}.</p>
                <a href="{{ route('admin.users.create', ['role' => $tab]) }}"
                   class="inline-flex items-center gap-1 mt-3 text-emerald-400 hover:text-emerald-300 text-sm transition-colors">
                    + Tambah {{ ucfirst(str_replace('_',' ',$tab)) }}
                </a>
            </div>
        @endif
    </div>

</x-simans-layout>
