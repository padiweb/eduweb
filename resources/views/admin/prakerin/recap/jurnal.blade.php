<x-simans-layout title="Rekap Jurnal Prakerin">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Rekap Jurnal Prakerin</h1>
        <p class="text-gray-500 text-sm mt-1">Semua jurnal harian siswa</p>
    </div>

    {{-- Sub-nav --}}
    <div class="tab-nav-scroll">
        <a href="{{ route('admin.prakerin.periods.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Periode</a>
        <a href="{{ route('admin.prakerin.locations.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">DU/DI</a>
        <a href="{{ route('admin.prakerin.placements.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Penempatan Siswa</a>
        <a href="{{ route('admin.prakerin.recap.absensi') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Rekap Absensi</a>
        <a href="{{ route('admin.prakerin.recap.jurnal') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-blue-600 text-white">Rekap Jurnal</a>
    </div>

    {{-- Filter --}}
    <div class="tab-nav-scroll">
        <div class="flex flex-wrap gap-2">
            @foreach ($periods as $p)
                <a href="{{ route('admin.prakerin.recap.jurnal', ['period_id' => $p->id]) }}"
                   class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors
                   {{ $periodId == $p->id ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-500 hover:text-gray-700' }}">
                    {{ $p->name }}
                </a>
            @endforeach
        </div>
        @if ($locations->count() > 0)
        <select onchange="window.location = '{{ route('admin.prakerin.recap.jurnal') }}?period_id={{ $periodId }}&location_id=' + this.value"
                class="bg-white border border-gray-200 text-gray-600 rounded-xl px-3 py-1.5 text-sm focus:outline-none">
            <option value="">Semua DU/DI</option>
            @foreach ($locations as $loc)
                <option value="{{ $loc->id }}" {{ $locId == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
            @endforeach
        </select>
        @endif
    </div>

    @forelse ($journals as $journal)
        <div class="bg-white border border-gray-200 rounded-xl p-4 mb-3">
            <div class="flex items-start justify-between mb-3 gap-3">
                <div>
                    <p class="text-gray-900 text-sm font-semibold">{{ $journal->student->name }}</p>
                    <p class="text-gray-500 text-xs mt-0.5">
                        {{ $journal->placement->location->name }} ·
                        {{ $journal->journal_date->translatedFormat('l, d F Y') }} ·
                        Dikirim {{ $journal->submitted_at->format('H:i') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if ($journal->teacher_note)
                        <span class="px-2 py-0.5 bg-blue-50 border border-blue-200 text-blue-400 text-xs rounded-lg">Ada catatan</span>
                    @endif
                    <span class="px-2 py-0.5 bg-amber-50 border border-amber-200 text-amber-600 text-xs rounded-lg">Terkirim</span>
                </div>
            </div>

            <p class="text-gray-600 text-sm leading-relaxed mb-3">{{ Str::limit($journal->content, 300) }}</p>

            @if ($journal->photos->count() > 0)
                <div class="flex gap-2 mb-3 overflow-x-auto pb-1">
                    @foreach ($journal->photos as $photo)
                        <a href="{{ Storage::url($photo->photo_path) }}" target="_blank" class="flex-shrink-0">
                            <img src="{{ Storage::url($photo->photo_path) }}" alt="{{ $photo->caption }}"
                                 style="width:64px;height:64px;object-fit:cover"
                                 class="rounded-xl border border-gray-200 hover:border-amber-200 transition-colors"/>
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($journal->teacher_note)
                <div class="p-3 bg-blue-500/5 border border-blue-200 rounded-xl">
                    <p class="text-blue-400 text-xs font-semibold mb-0.5">Catatan Guru:</p>
                    <p class="text-blue-300 text-xs">{{ $journal->teacher_note }}</p>
                </div>
            @else
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="text-gray-500 hover:text-gray-600 text-xs transition-colors">
                        + Tambah catatan
                    </button>
                    <div x-show="open" x-cloak class="mt-2">
                        <form action="{{ route('admin.prakerin.placements.journal.note', $journal) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="teacher_note" placeholder="Catatan untuk jurnal ini..."
                                   class="flex-1 bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-blue-400 placeholder-gray-400">
                            <button type="submit" class="px-4 py-2 bg-blue-50 hover:bg-blue-600/40 text-blue-400 text-xs rounded-xl border border-blue-200 transition-colors">
                                Simpan
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    @empty
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-500">Belum ada jurnal untuk filter ini.</p>
        </div>
    @endforelse

    <div class="mt-4">{{ $journals->links() }}</div>

</x-simans-layout>
