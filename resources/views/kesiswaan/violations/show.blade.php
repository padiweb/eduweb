<x-simans-layout title="Detail Pelanggaran Siswa">

    <div class="flex items-start justify-between mb-6">
        <div>
            <a href="{{ route('kesiswaan.violations.index') }}"
               class="flex items-center gap-1 text-gray-500 hover:text-gray-900 text-sm mb-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $student->name }}</h1>
            <p class="text-gray-500 text-sm mt-0.5">NIS: {{ $student->nis }}</p>
        </div>

        {{-- Total poin besar --}}
        @php $color = $totalPoints >= 20 ? 'red' : ($totalPoints >= 10 ? 'amber' : 'emerald'); @endphp
        <div class="text-center bg-white border border-{{ $color }}-500/20 rounded-xl px-6 py-4">
            <p class="text-3xl font-bold text-{{ $color }}-400">{{ $totalPoints }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Poin</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-900/30 border border-red-700/40 text-red-300 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form input pelanggaran manual --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-amber-500/20 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    Catat Pelanggaran
                </h2>

                <form method="POST"
                      action="{{ route('kesiswaan.violations.store') }}"
                      enctype="multipart/form-data"
                      class="space-y-3">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">

                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Kategori <span class="text-red-400">*</span></label>
                        <select name="category_id" required
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-500 transition-colors">
                            <option value="">Pilih kategori...</option>
                            @foreach($categories->where('school_id', auth()->user()->school_id) as $cat)
                                <option value="{{ $cat->id }}"
                                        data-points="{{ $cat->default_points }}">
                                    {{ $cat->name }} ({{ $cat->default_points }} poin)
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">
                            Poin <span class="text-red-400">*</span>
                            <span class="text-gray-400">(default dari kategori, bisa diubah)</span>
                        </label>
                        <input type="number" name="points" id="input-points" min="1" max="100" required
                               value="{{ old('points', 1) }}"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-500 transition-colors">
                        @error('points') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Tanggal Kejadian <span class="text-red-400">*</span></label>
                        <input type="date" name="incident_date" required
                               value="{{ old('incident_date', today()->format('Y-m-d')) }}"
                               max="{{ today()->format('Y-m-d') }}"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-500 transition-colors">
                        @error('incident_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Keterangan <span class="text-red-400">*</span></label>
                        <textarea name="description" rows="3" required
                                  class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-500 resize-none transition-colors"
                                  placeholder="Deskripsi pelanggaran...">{{ old('description') }}</textarea>
                        @error('description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Bukti (foto/PDF, opsional)</label>
                        <input type="file" name="evidence_path"
                               accept="image/jpeg,image/png,application/pdf"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-500 transition-colors file:mr-3 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-gray-100 file:text-gray-600">
                        @error('evidence_path') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                            class="w-full bg-amber-500 hover:bg-amber-600 text-gray-900 text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Catat Pelanggaran
                    </button>
                </form>
            </div>
        </div>

        {{-- Daftar pelanggaran --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">Riwayat Pelanggaran</h2>
                    <span class="text-xs text-gray-400">{{ $violations->count() }} catatan</span>
                </div>

                @if($violations->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($violations as $v)
                            @php
                                $isAuto = $v->isAutomatic();
                                $sourceColors = [
                                    'manual'             => 'bg-red-500/10 border-red-500/20 text-red-400',
                                    'absen_terlambat'    => 'bg-amber-500/10 border-amber-500/20 text-amber-400',
                                    'absen_alfa'         => 'bg-red-500/10 border-red-500/20 text-red-400',
                                    'tugas_terlambat'    => 'bg-amber-500/10 border-amber-500/20 text-amber-400',
                                    'tugas_tidak_kumpul' => 'bg-orange-500/10 border-orange-500/20 text-orange-400',
                                ];
                                $badgeClass = $sourceColors[$v->source] ?? 'bg-white text-gray-500 border-gray-200';
                            @endphp
                            <div class="flex items-start gap-3 px-5 py-4">

                                {{-- Poin --}}
                                <div class="w-9 h-9 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="text-sm font-bold text-red-400">{{ $v->points }}</span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-medium text-gray-900">{{ $v->category->name }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $badgeClass }}">
                                            {{ $v->source_label }}
                                        </span>
                                        @if($isAuto)
                                            <span class="text-xs text-gray-400">Otomatis</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $v->description }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $v->incident_date->translatedFormat('d F Y') }}
                                        @if(! $isAuto)
                                            · {{ $v->reportedBy->name }}
                                        @endif
                                    </p>
                                </div>

                                {{-- Arsipkan (hanya manual) --}}
                                @if(! $isAuto)
                                    <form method="POST"
                                          action="{{ route('kesiswaan.violations.archive', $v->id) }}"
                                          onsubmit="return confirm('Arsipkan pelanggaran ini?')">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="text-xs text-gray-400 hover:text-red-400 transition-colors py-1 px-2 rounded-lg hover:bg-red-500/10"
                                                title="Arsipkan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <div class="w-8 flex-shrink-0">
                                        <span class="text-xs text-gray-900" title="Pelanggaran otomatis tidak bisa diarsipkan manual">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                            </svg>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-12 text-center">
                        <p class="text-gray-400 text-sm">Belum ada pelanggaran tercatat.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
    // Auto-fill poin dari kategori yang dipilih
    document.querySelector('select[name="category_id"]')?.addEventListener('change', function() {
        var opt    = this.options[this.selectedIndex];
        var pts    = opt.getAttribute('data-points');
        var input  = document.getElementById('input-points');
        if (pts && input) input.value = pts;
    });
    </script>

</x-simans-layout>
