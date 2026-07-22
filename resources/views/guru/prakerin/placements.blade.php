<x-simans-layout title="Penempatan Siswa - Prakerin">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Penempatan Siswa</h1>
            <p class="text-gray-500 text-sm mt-1">Assign siswa ke DU/DI yang Anda bimbing</p>
        </div>
        @if ($period && $locations->count() > 0)
        <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-gray-900 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Penempatan
        </button>
        @endif
    </div>

    {{-- Sub-nav --}}
    <div class="flex gap-2 mb-5 flex-wrap">
        <a href="{{ route('guru.prakerin.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Dashboard</a>
        <a href="{{ route('guru.prakerin.locations') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">DU/DI Saya</a>
        <a href="{{ route('guru.prakerin.placements') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-blue-600 text-gray-900">Penempatan Siswa</a>
        <a href="{{ route('guru.prakerin.recap.absensi') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Rekap Absensi</a>
        <a href="{{ route('guru.prakerin.recap.jurnal') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">Rekap Jurnal</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl bg-blue-600/10 border border-blue-200 text-blue-600 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
    @endif

    @if (! $period)
        <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center">
            <p class="text-gray-500">Anda belum ditunjuk sebagai koordinator di periode manapun.</p>
        </div>
    @elseif ($locations->isEmpty())
        <div class="bg-amber-500/5 border border-amber-500/20 rounded-2xl p-5">
            <p class="text-amber-400 text-sm">Belum ada DU/DI yang Anda bimbing. <a href="{{ route('guru.prakerin.locations') }}" class="underline">Tambah DU/DI dulu</a>.</p>
        </div>
    @elseif ($placements->isEmpty())
        <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center">
            <p class="text-gray-500">Belum ada siswa yang ditempatkan di lokasi Anda.</p>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs text-gray-400 font-medium px-5 py-3">Siswa</th>
                        <th class="text-left text-xs text-gray-400 font-medium px-5 py-3">DU/DI</th>
                        <th class="text-left text-xs text-gray-400 font-medium px-5 py-3 hidden md:table-cell">Periode Siswa</th>
                        <th class="text-left text-xs text-gray-400 font-medium px-5 py-3">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($placements as $p)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3">
                                <p class="text-gray-900 font-medium">{{ $p->student->name }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $p->location->name }}</td>
                            <td class="px-5 py-3 hidden md:table-cell text-gray-500 text-xs">
                                @if ($p->start_date || $p->end_date)
                                    {{ $p->start_date?->format('d M') ?? '—' }} – {{ $p->end_date?->format('d M Y') ?? '—' }}
                                @else
                                    <span class="text-gray-400">Ikut periode</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($p->isActiveToday())
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-blue-600/10 text-blue-600 border border-blue-200">Aktif</span>
                                @elseif ($p->is_active)
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">Terjadwal</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('guru.prakerin.placements.show', $p) }}"
                                       class="px-3 py-1.5 bg-white hover:bg-gray-100 border border-gray-200 text-gray-600 text-xs rounded-lg transition-colors">
                                        Rekap
                                    </a>
                                    <form action="{{ route('guru.prakerin.placements.destroy', $p) }}" method="POST"
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
        <div class="bg-white border border-gray-200 rounded-2xl p-6 w-full max-w-md">
            <h2 class="text-gray-900 font-semibold mb-4">Tambah Penempatan Siswa</h2>
            <form action="{{ route('guru.prakerin.placements.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="period_id" value="{{ $period?->id }}">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Siswa <span class="text-red-400">*</span></label>
                    <select name="student_id" required class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                        <option value="">— Pilih Siswa —</option>
                        @foreach ($students as $s)
                            @php $sudahDitempatkan = $placedStudents->get($s->id); @endphp
                            <option value="{{ $s->id }}"
                                {{ old('student_id') == $s->id ? 'selected' : '' }}
                                {{ $sudahDitempatkan ? 'disabled' : '' }}>
                                {{ $s->name }}{{ $sudahDitempatkan ? ' — sudah di ' . $sudahDitempatkan->location->name : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if ($placedStudents->count() > 0)
                        <p class="text-gray-400 text-xs mt-1">Siswa yang sudah ditempatkan ditandai dan tidak bisa dipilih.</p>
                    @endif
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">DU/DI <span class="text-red-400">*</span></label>
                    <select name="location_id" required class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                        <option value="">— Pilih DU/DI —</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="text-gray-400 text-xs">Tanggal override (kosongkan = ikut tanggal periode)</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Mulai (opsional)</label>
                        <input type="date" name="start_date" class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Selesai (opsional)</label>
                        <input type="date" name="end_date" class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                            class="flex-1 py-2.5 bg-white border border-gray-200 text-gray-600 text-sm rounded-xl">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-gray-900 text-sm font-semibold rounded-xl">Simpan</button>
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
