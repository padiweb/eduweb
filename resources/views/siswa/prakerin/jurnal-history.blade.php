<x-simans-layout title="Riwayat Jurnal Prakerin">
    <div class="mb-5">
        <a href="{{ route('siswa.prakerin.index') }}" class="text-gray-500 text-sm hover:text-white flex items-center gap-1 mb-3 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <h1 class="text-xl font-bold text-white">Riwayat Jurnal</h1>
        <p class="text-gray-400 text-sm mt-0.5">{{ $placement->location->name }}</p>
    </div>

    @forelse ($journals as $journal)
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-4 mb-3">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-white text-sm font-semibold">{{ $journal->journal_date->translatedFormat('l, d F Y') }}</p>
                    <p class="text-gray-500 text-xs mt-0.5">Dikirim {{ $journal->submitted_at->format('H:i') }}</p>
                </div>
                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">Terkirim</span>
            </div>
            <p class="text-gray-300 text-sm leading-relaxed mb-3">{{ Str::limit($journal->content, 200) }}</p>
            @if ($journal->photos->count() > 0)
                <div class="flex gap-2 mb-3 overflow-x-auto pb-1">
                    @foreach ($journal->photos as $photo)
                        <a href="{{ Storage::url($photo->photo_path) }}" target="_blank" class="flex-shrink-0">
                            <img src="{{ Storage::url($photo->photo_path) }}" alt="{{ $photo->caption }}"
                                 style="width:72px;height:72px;object-fit:cover" class="rounded-xl border border-white/10"/>
                        </a>
                    @endforeach
                </div>
            @endif
            @if ($journal->teacher_note)
                <div class="p-3 bg-blue-500/5 border border-blue-500/15 rounded-xl">
                    <p class="text-blue-400 text-xs font-semibold mb-0.5">Catatan Pembimbing:</p>
                    <p class="text-blue-300 text-xs">{{ $journal->teacher_note }}</p>
                </div>
            @endif
        </div>
    @empty
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-12 text-center">
            <p class="text-gray-500">Belum ada jurnal yang diisi.</p>
        </div>
    @endforelse
    <div class="mt-4">{{ $journals->links() }}</div>
</x-simans-layout>
