<x-simans-layout title="Tahun Ajaran">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Tahun Ajaran</h1>
            <p class="text-gray-400 text-sm mt-1">Kelola tahun ajaran dan semester aktif</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form tambah --}}
        <div>
            <div class="bg-gray-900 border border-emerald-500/20 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-white mb-4">Tambah Tahun Ajaran</h2>
                <form method="POST" action="{{ route('admin.academic-years.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Tahun Ajaran <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required
                               placeholder="cth: 2025/2026"
                               value="{{ old('name') }}"
                               class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Semester <span class="text-red-400">*</span></label>
                        <select name="semester" required
                                class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <option value="1" {{ old('semester') == '1' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                            <option value="2" {{ old('semester') == '2' ? 'selected' : '' }}>Semester 2 (Genap)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Tanggal Mulai <span class="text-red-400">*</span></label>
                        <input type="date" name="start_date" required value="{{ old('start_date') }}"
                               class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Tanggal Selesai <span class="text-red-400">*</span></label>
                        <input type="date" name="end_date" required value="{{ old('end_date') }}"
                               class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                    </div>
                    <button type="submit"
                            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Tambah Tahun Ajaran
                    </button>
                </form>
            </div>

            <div class="mt-4 bg-gray-900 border border-white/5 rounded-xl p-4">
                <p class="text-xs text-gray-500 leading-relaxed">
                    Hanya <span class="text-white font-medium">satu tahun ajaran</span> yang bisa aktif sekaligus.
                    Tahun ajaran aktif menentukan kelas, absensi, tugas, dan nilai yang sedang berjalan.
                </p>
            </div>
        </div>

        {{-- Daftar tahun ajaran --}}
        <div class="lg:col-span-2">
            <div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Semua Tahun Ajaran</h2>
                    <span class="text-xs text-gray-500">{{ $academicYears->count() }} tahun ajaran</span>
                </div>

                @if($academicYears->count() > 0)
                    <div class="divide-y divide-white/5">
                        @foreach($academicYears as $ay)
                            <div class="flex items-center gap-4 px-5 py-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-semibold text-white">{{ $ay->name }}</p>
                                        <span class="text-xs {{ $ay->semester == 1 ? 'text-blue-400 bg-blue-500/10 border-blue-500/20' : 'text-purple-400 bg-purple-500/10 border-purple-500/20' }} border px-2 py-0.5 rounded-full">
                                            Semester {{ $ay->semester }} ({{ $ay->semester == 1 ? 'Ganjil' : 'Genap' }})
                                        </span>
                                        @if($ay->is_active)
                                            <span class="text-xs text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full font-semibold">
                                                Aktif
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $ay->start_date->translatedFormat('d M Y') }} &ndash; {{ $ay->end_date->translatedFormat('d M Y') }}
                                        &middot; {{ $ay->classrooms_count }} kelas
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if(! $ay->is_active)
                                        <form method="POST" action="{{ route('admin.academic-years.activate', $ay->id) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="text-xs text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 px-3 py-1.5 rounded-lg transition-colors">
                                                Aktifkan
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.academic-years.destroy', $ay->id) }}"
                                              onsubmit="return confirm('Hapus tahun ajaran {{ addslashes($ay->label) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-800 hover:bg-red-900/40 border border-white/10 hover:border-red-500/30 text-gray-400 hover:text-red-400 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-600 italic">Sedang aktif</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-12 text-center">
                        <p class="text-gray-500 text-sm">Belum ada tahun ajaran. Tambahkan di form sebelah kiri.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-simans-layout>
