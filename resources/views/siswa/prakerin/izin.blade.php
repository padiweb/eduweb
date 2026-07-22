<x-simans-layout title="Ajukan Ketidakhadiran">
    <div class="mb-5">
        <a href="{{ route('siswa.prakerin.index') }}" class="text-gray-500 text-sm hover:text-gray-900 flex items-center gap-1 mb-3 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <h1 class="text-xl font-bold text-gray-900">Konfirmasi Tidak Hadir</h1>
        <p class="text-gray-500 text-sm mt-0.5">
            {{ $placement->location->name }} &middot; {{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}
        </p>
    </div>

    @if ($absence)
        {{-- Sudah lapor hari ini --}}
        <div class="bg-white border border-orange-500/20 rounded-xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-orange-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-900 font-semibold text-sm">Ketidakhadiran tercatat</p>
                    <p class="text-blue-600 text-xs mt-0.5">Anda tidak akan tercatat alfa hari ini</p>
                </div>
            </div>
            <div class="space-y-2 pt-3 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500 text-sm">Jenis</span>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-lg
                        {{ $absence->type === 'sakit' ? 'bg-red-500/10 text-red-400' :
                           ($absence->type === 'libur' ? 'bg-blue-500/10 text-blue-400' :
                           'bg-orange-500/10 text-orange-400') }}">
                        {{ $absence->type_label }}
                    </span>
                </div>
                <div class="pt-2 border-t border-gray-200">
                    <p class="text-gray-500 text-xs mb-1">Keterangan:</p>
                    <p class="text-gray-400 text-sm">{{ $absence->reason }}</p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 mb-5 flex items-start gap-3">
            <svg class="w-4 h-4 text-orange-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-orange-300 text-xs leading-relaxed">
                Jika tidak hadir tanpa laporan, akan otomatis tercatat <strong>alfa</strong> dan mendapat poin pelanggaran.
                Lapor sebelum hari berakhir agar tidak tercatat alfa.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm space-y-0.5">
                @foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <form action="{{ route('siswa.prakerin.izin.store') }}" method="POST" enctype="multipart/form-data"
              class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            @csrf

            {{-- Pilih jenis --}}
            <div class="p-5 border-b border-gray-200">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Jenis Ketidakhadiran</p>
                <div class="grid grid-cols-3 gap-3" x-data="{ selected: '{{ old('type', 'izin') }}' }">
                    @foreach (['izin' => 'Izin', 'sakit' => 'Sakit', 'libur' => 'Libur DU/DI'] as $val => $label)
                        <label class="cursor-pointer" @click="selected = '{{ $val }}'">
                            <input type="radio" name="type" value="{{ $val }}" class="sr-only"
                                   x-model="selected">
                            <div class="p-3 rounded-xl border text-center transition-all"
                                 :class="selected === '{{ $val }}'
                                    ? 'border-orange-500/50 bg-orange-500/10'
                                    : 'border-gray-200 bg-white hover:border-white/20'">
                                <p class="text-sm font-medium transition-colors"
                                   :class="selected === '{{ $val }}' ? 'text-orange-400' : 'text-gray-400'">
                                    {{ $label }}
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Keterangan --}}
            <div class="p-5 border-b border-gray-200">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Keterangan</p>
                <textarea name="reason" rows="4" required minlength="10" maxlength="500"
                          placeholder="Jelaskan alasan tidak hadir hari ini..."
                          class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-3 text-sm placeholder-gray-400 focus:outline-none focus:border-orange-500/50 resize-none">{{ old('reason') }}</textarea>
                <p class="text-gray-500 text-xs mt-1">Minimal 10 karakter</p>
            </div>

            {{-- Lampiran --}}
            <div class="p-5 border-b border-gray-200">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
                    Lampiran <span class="text-gray-500 font-normal">(Opsional — surat dokter, dll)</span>
                </p>
                <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf"
                       class="block w-full text-sm text-gray-500
                              file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0
                              file:text-sm file:bg-white file:text-gray-400
                              hover:file:bg-gray-100 cursor-pointer">
                <p class="text-gray-500 text-xs mt-1">JPG, PNG, atau PDF. Maks 5MB.</p>
            </div>

            {{-- Submit --}}
            <div class="p-5">
                <button type="submit"
                        class="w-full py-3 bg-orange-700 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition-colors">
                    Kirim Konfirmasi
                </button>
            </div>
        </form>
    @endif
</x-simans-layout>
