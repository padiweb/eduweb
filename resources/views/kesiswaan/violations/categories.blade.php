<x-simans-layout title="Kategori Pelanggaran">

    <div class="flex items-start justify-between mb-6">
        <div>
            <a href="{{ route('kesiswaan.violations.index') }}"
               class="flex items-center gap-1 text-gray-500 hover:text-blue-600 text-sm mb-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Kategori Pelanggaran</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola kategori untuk input pelanggaran manual</p>
        </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form tambah kategori --}}
        <div>
            <div class="bg-white border border-blue-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Tambah Kategori Baru</h2>

                <form method="POST" action="{{ route('kesiswaan.violations.categories.store') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Nama Kategori <span class="text-red-600">*</span></label>
                        <input type="text" name="name" required
                               value="{{ old('name') }}"
                               placeholder="cth: Perkelahian, Bolos, dll"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Tingkat Keparahan <span class="text-red-600">*</span></label>
                        <select name="severity" required
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">Pilih tingkat...</option>
                            <option value="ringan"  {{ old('severity') === 'ringan'  ? 'selected' : '' }}>Ringan</option>
                            <option value="sedang"  {{ old('severity') === 'sedang'  ? 'selected' : '' }}>Sedang</option>
                            <option value="berat"   {{ old('severity') === 'berat'   ? 'selected' : '' }}>Berat</option>
                        </select>
                        @error('severity') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Poin Default <span class="text-red-600">*</span></label>
                        <input type="number" name="default_points" required min="1" max="100"
                               value="{{ old('default_points', 5) }}"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        <p class="text-xs text-gray-500 mt-1">Bisa diubah saat input pelanggaran</p>
                        @error('default_points') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Tambah Kategori
                    </button>
                </form>
            </div>
        </div>

        {{-- Daftar kategori --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">Semua Kategori</h2>
                    <span class="text-xs text-gray-500">{{ $categories->count() }} kategori</span>
                </div>

                @if($categories->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($categories as $cat)
                            @php
                                $severityMap = [
                                    'ringan' => ['bg-blue-50 text-blue-600 border-blue-200', 'Ringan'],
                                    'sedang' => ['bg-amber-50 text-amber-700 border-amber-200',   'Sedang'],
                                    'berat'  => ['bg-red-50 text-red-700 border-red-200',         'Berat'],
                                ];
                                [$badgeClass, $severityLabel] = $severityMap[$cat->severity] ?? ['bg-white text-gray-500 border-gray-200', $cat->severity];
                            @endphp
                            <div class="flex items-center gap-4 px-5 py-4">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $cat->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Digunakan {{ $cat->violations_count }} kali
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeClass }} flex-shrink-0">
                                    {{ $severityLabel }}
                                </span>
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white border border-gray-200 flex-shrink-0">
                                    <span class="text-sm font-bold text-gray-900">{{ $cat->default_points }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-12 text-center">
                        <svg class="w-12 h-12 text-blue-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                        </svg>
                        <p class="text-gray-500 text-sm">Belum ada kategori pelanggaran.</p>
                        <p class="text-gray-500 text-xs mt-1">Tambahkan kategori di form sebelah kiri.</p>
                    </div>
                @endif
            </div>

            {{-- Info kategori otomatis --}}
            <div class="mt-4 bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs font-semibold text-gray-500 mb-2">Kategori Otomatis dari Sistem</p>
                <div class="space-y-1.5">
                    @foreach([
                        ['Keterlambatan',            1, 'amber'],
                        ['Alfa Tanpa Keterangan',    3, 'red'],
                        ['Terlambat Mengumpulkan Tugas', 1, 'amber'],
                        ['Tidak Mengumpulkan Tugas', 2, 'orange'],
                    ] as [$name, $pts, $color])
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">{{ $name }}</span>
                            <span class="text-xs font-semibold text-{{ $color }}-400">{{ $pts }} poin</span>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-2">Kategori ini dibuat otomatis oleh sistem dan tidak bisa dihapus.</p>
            </div>
        </div>
    </div>

</x-simans-layout>