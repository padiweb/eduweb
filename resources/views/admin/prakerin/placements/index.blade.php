<x-simans-layout title="Penempatan Siswa Prakerin">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Penempatan Siswa</h1>
            <p class="text-gray-400 text-sm mt-1">Assign siswa ke DU/DI dalam periode prakerin</p>
        </div>
        @if ($activePeriod && $locations->count() > 0)
        <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
                class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Penempatan
        </button>
        @endif
    </div>

    {{-- Sub-nav --}}
    <div class="flex gap-2 mb-5 flex-wrap">
        <a href="{{ route('admin.prakerin.periods.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Periode</a>
        <a href="{{ route('admin.prakerin.locations.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">DU/DI</a>
        <a href="{{ route('admin.prakerin.placements.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-emerald-500 text-white">Penempatan Siswa</a>
        <a href="{{ route('admin.prakerin.recap.absensi') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Rekap Absensi</a>
        <a href="{{ route('admin.prakerin.recap.jurnal') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Rekap Jurnal</a>
    </div>

    {{-- Filter periode --}}
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach ($periods as $p)
            <a href="{{ route('admin.prakerin.placements.index', ['period_id' => $p->id]) }}"
               class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2
               {{ $periodId == $p->id ? 'bg-emerald-500 text-white' : 'bg-gray-900 border border-white/10 text-gray-400 hover:text-white' }}">
                {{ $p->name }}
                @if ($p->isOngoing()) <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span> @endif
            </a>
        @endforeach
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
    @endif

    @if (! $activePeriod)
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-8 text-center">
            <p class="text-gray-400">Pilih periode di atas atau <a href="{{ route('admin.prakerin.periods.index') }}" class="text-emerald-400 hover:underline">buat periode baru</a>.</p>
        </div>
    @elseif ($locations->isEmpty())
        <div class="bg-amber-500/5 border border-amber-500/20 rounded-2xl p-5 mb-4">
            <p class="text-amber-400 text-sm">Belum ada DU/DI untuk periode ini. <a href="{{ route('admin.prakerin.locations.index', ['period_id' => $periodId]) }}" class="underline">Tambah DU/DI dulu</a>.</p>
        </div>
    @elseif ($placements->isEmpty())
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-12 text-center">
            <p class="text-gray-400">Belum ada siswa yang ditempatkan di periode ini.</p>
        </div>
    @else
        <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">Siswa</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">DU/DI</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3 hidden md:table-cell">Periode Siswa</th>
                        <th class="text-left text-xs text-gray-500 font-medium px-5 py-3">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($placements as $p)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-3">
                                <p class="text-white font-medium">{{ $p->student->name }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-gray-200">{{ $p->location->name }}</p>
                                @if ($p->location->supervisors->count() > 0)
                                    <p class="text-gray-500 text-xs">Pembimbing: {{ $p->location->supervisors->pluck('name')->join(', ') }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 hidden md:table-cell text-gray-400 text-xs">
                                @if ($p->start_date || $p->end_date)
                                    {{ $p->start_date?->format('d M') ?? '—' }} – {{ $p->end_date?->format('d M Y') ?? '—' }}
                                @else
                                    <span class="text-gray-600">Ikut periode</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($p->isActiveToday())
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Aktif</span>
                                @elseif ($p->is_active)
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">Terjadwal</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-gray-700 text-gray-400">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('admin.prakerin.placements.show', $p) }}"
                                       class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-300 text-xs rounded-lg transition-colors">
                                        Rekap
                                    </a>
                                    <form action="{{ route('admin.prakerin.placements.destroy', $p) }}" method="POST"
                                          onsubmit="return confirm('Hapus penempatan {{ $p->student->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-red-600/10 hover:bg-red-600/20 border border-red-500/20 text-red-400 text-xs rounded-lg transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $placements->links() }}</div>
    @endif

    {{-- Modal Tambah --}}
    <div id="modal-tambah" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-gray-950 border border-white/10 rounded-2xl p-6 w-full max-w-md">
            <h2 class="text-white font-semibold mb-4">Tambah Penempatan Siswa</h2>
            <form action="{{ route('admin.prakerin.placements.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="period_id" value="{{ $periodId }}">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Siswa <span class="text-red-400">*</span></label>
                    <select name="student_id" required class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                        <option value="">— Pilih Siswa —</option>
                        @foreach ($students as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">DU/DI <span class="text-red-400">*</span></label>
                    <select name="location_id" required class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                        <option value="">— Pilih DU/DI —</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="text-gray-600 text-xs">Tanggal override (kosongkan = ikut tanggal periode)</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Mulai (opsional)</label>
                        <input type="date" name="start_date" class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Selesai (opsional)</label>
                        <input type="date" name="end_date" class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 resize-none placeholder-gray-600" placeholder="Catatan opsional"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                            class="flex-1 py-2.5 bg-gray-800 border border-white/10 text-gray-300 text-sm rounded-xl">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('modal-tambah')?.addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
    </script>

</x-simans-layout>
