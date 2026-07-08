<x-simans-layout title="Promosi Siswa">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Promosi Siswa</h1>
        <p class="text-gray-400 text-sm mt-1">Proses kenaikan kelas, kelulusan, atau perubahan status siswa massal</p>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-300 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form pilih tahun ajaran sumber --}}
        <div class="lg:col-span-1">
            <div class="bg-gray-900 border border-emerald-500/20 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-white mb-1">Pilih Tahun Ajaran Sumber</h2>
                <p class="text-xs text-gray-500 mb-4">Pilih tahun ajaran yang siswa-siswanya akan dipromosikan.</p>

                <form method="POST" action="{{ route('admin.promotions.load-source') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Tahun Ajaran Asal <span class="text-red-400">*</span></label>
                        <select name="source_year_id" required
                                class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <option value="">Pilih tahun ajaran...</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}">
                                    {{ $ay->label }}
                                    @if($ay->is_active) (Aktif) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Tampilkan Siswa
                    </button>
                </form>
            </div>

            {{-- Info tahun ajaran aktif --}}
            @if($activeYear)
                <div class="mt-4 bg-gray-900 border border-white/5 rounded-xl p-4">
                    <p class="text-xs text-gray-500 mb-2">Tahun Ajaran Aktif (Tujuan)</p>
                    <p class="text-sm font-semibold text-emerald-400">{{ $activeYear->label }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $activeYear->start_date->translatedFormat('d M Y') }} &ndash;
                        {{ $activeYear->end_date->translatedFormat('d M Y') }}
                    </p>
                    <p class="text-xs text-gray-600 mt-2">Siswa yang naik kelas akan dipindahkan ke kelas di tahun ajaran ini.</p>
                </div>
            @else
                <div class="mt-4 bg-amber-900/20 border border-amber-500/30 rounded-xl p-4">
                    <p class="text-xs text-amber-400 font-semibold mb-1">Belum ada tahun ajaran aktif!</p>
                    <p class="text-xs text-amber-300/70">Aktifkan tahun ajaran tujuan terlebih dahulu di menu
                        <a href="{{ route('admin.academic-years.index') }}" class="underline">Tahun Ajaran</a>.
                    </p>
                </div>
            @endif

            {{-- Panduan --}}
            <div class="mt-4 bg-gray-900 border border-white/5 rounded-xl p-4 space-y-2">
                <p class="text-xs font-semibold text-gray-400">Keterangan Aksi:</p>
                @foreach([
                    ['emerald', 'Naik Kelas', 'Pindah ke kelas lebih tinggi di tahun ajaran aktif'],
                    ['amber',   'Tidak Naik', 'Tetap di grade yang sama, pindah ke kelas lain di tahun ajaran aktif'],
                    ['blue',    'Lulus',      'Tamat sekolah, status jadi Alumni, akun nonaktif'],
                    ['red',     'Keluar',     'Keluar/DO, akun nonaktif, data tetap tersimpan'],
                    ['purple',  'Pindah',     'Pindah ke sekolah lain, akun nonaktif'],
                ] as [$color, $label, $desc])
                    <div class="flex items-start gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-{{ $color }}-400 flex-shrink-0 mt-1.5"></span>
                        <div>
                            <span class="text-xs font-semibold text-{{ $color }}-400">{{ $label }}</span>
                            <span class="text-xs text-gray-600"> — {{ $desc }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Placeholder --}}
        <div class="lg:col-span-2">
            <div class="bg-gray-900 border border-white/5 rounded-xl p-12 text-center h-full flex items-center justify-center">
                <div>
                    <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
                    </svg>
                    <p class="text-gray-500 text-sm">Pilih tahun ajaran sumber di sebelah kiri</p>
                    <p class="text-gray-600 text-xs mt-1">Daftar siswa per kelas akan muncul di sini</p>
                </div>
            </div>
        </div>
    </div>

</x-simans-layout>
