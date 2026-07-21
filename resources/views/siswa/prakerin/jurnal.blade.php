<x-simans-layout title="Jurnal Harian Prakerin">
    <div class="mb-5">
        <a href="{{ route('siswa.prakerin.index') }}" class="text-gray-500 text-sm hover:text-white flex items-center gap-1 mb-3 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <h1 class="text-xl font-bold text-white">Jurnal Harian</h1>
        <p class="text-gray-400 text-sm mt-0.5">
            {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }} &middot; {{ $placement->location->name }}
        </p>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
            @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
        </div>
    @endif

    @if ($isLate)
        <div class="mb-4 p-3 rounded-xl bg-amber-500/5 border border-amber-500/15 flex items-start gap-2">
            <svg class="w-4 h-4 text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-amber-300 text-xs font-semibold">Mengisi jurnal hari yang sudah lewat</p>
                <p class="text-amber-400/70 text-xs mt-0.5">Poin pelanggaran yang sudah diterima tidak dapat dikurangi, namun jurnal tetap bisa disimpan.</p>
            </div>
        </div>
    @elseif ($journal)
        <div class="mb-4 p-3 rounded-xl bg-amber-500/5 border border-amber-500/15 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-amber-300 text-xs">Jurnal sudah diisi pukul {{ $journal->submitted_at?->format('H:i') ?? $journal->updated_at->format('H:i') }}. Anda bisa memperbarui isinya.</p>
        </div>
    @else
        <div class="mb-4 p-3 rounded-xl bg-blue-500/5 border border-blue-500/15 flex items-start gap-2">
            <svg class="w-4 h-4 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-blue-300 text-xs">Isi jurnal setiap hari. Batas waktu sampai <strong>23:59 malam ini</strong>. Jurnal yang tidak diisi akan menghasilkan poin pelanggaran.</p>
        </div>
    @endif

    <form action="{{ route('siswa.prakerin.jurnal.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        {{-- Kirim tanggal jurnal --}}
        <input type="hidden" name="journal_date" value="{{ $date }}">

        <div class="mb-4">
            <label class="block text-sm text-gray-300 font-medium mb-2">Laporan Kegiatan <span class="text-red-400">*</span></label>
            <textarea name="content" rows="8" required minlength="50"
                      placeholder="Tuliskan kegiatan yang kamu lakukan hari ini di {{ $placement->location->name }}. Minimal 50 karakter."
                      class="w-full bg-gray-900 border border-white/10 text-white rounded-xl px-4 py-3 text-sm placeholder-gray-600 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/20 resize-none">{{ old('content', $journal?->content) }}</textarea>
            <p class="text-gray-600 text-xs mt-1">Minimal 50 karakter</p>
        </div>

        <div class="mb-6">
            <label class="block text-sm text-gray-300 font-medium mb-2">
                Foto Dokumentasi <span class="text-gray-500 font-normal">(opsional, maks. 5 foto)</span>
            </label>
            @if ($journal && $journal->photos->count() > 0)
                <div class="grid grid-cols-3 gap-2 mb-3">
                    @foreach ($journal->photos as $photo)
                        <div class="relative group">
                            <img src="{{ Storage::url($photo->photo_path) }}" alt="{{ $photo->caption }}"
                                 style="width:100%;aspect-ratio:1;object-fit:cover" class="rounded-xl border border-white/10"/>
                            <form action="{{ route('siswa.prakerin.jurnal.photo.delete', $photo) }}" method="POST" class="absolute top-1 right-1">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus foto?')"
                                        class="w-6 h-6 bg-red-600/80 rounded-md flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
            <div id="photo-list" class="space-y-2"></div>
            <button type="button" onclick="addPhotoInput()"
                    class="mt-2 w-full py-3 border border-dashed border-white/15 rounded-xl text-gray-500 text-sm hover:border-amber-500/40 hover:text-amber-400 transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Foto
            </button>
        </div>

        <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-500 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan Jurnal
        </button>
    </form>

    <script>
        let photoCount=0;
        function addPhotoInput() {
            if(photoCount>=5) { alert('Maksimal 5 foto.'); return; }
            const i=photoCount++;
            const div=document.createElement('div'); div.id='pr-'+i; div.className='flex items-start gap-2';
            div.innerHTML=`<div class="flex-1 space-y-1">
                <input type="file" name="photos[${i}]" accept="image/*"
                       class="block w-full text-sm text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-amber-600 file:text-white hover:file:bg-amber-500 cursor-pointer"/>
                <input type="text" name="captions[${i}]" placeholder="Keterangan (opsional)"
                       class="w-full bg-gray-900 border border-white/10 text-white rounded-lg px-3 py-1.5 text-xs placeholder-gray-600 focus:outline-none focus:border-amber-500/50"/>
            </div>
            <button type="button" onclick="document.getElementById('pr-${i}').remove();photoCount=Math.max(0,photoCount-1)"
                    class="mt-1 w-8 h-8 bg-gray-800 hover:bg-red-600/20 text-gray-500 hover:text-red-400 rounded-lg flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>`;
            document.getElementById('photo-list').appendChild(div);
        }
    </script>
</x-simans-layout>
