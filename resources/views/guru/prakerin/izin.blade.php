<x-simans-layout title="Konfirmasi Ketidakhadiran - Prakerin">

    <div class="mb-5">
        <h1 class="text-xl font-bold text-white">Konfirmasi Ketidakhadiran</h1>
        <p class="text-gray-400 text-sm mt-0.5">Setujui atau tolak pengajuan izin/sakit/libur siswa</p>
    </div>

    {{-- Sub-nav --}}
    <div class="flex gap-2 mb-5 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none">
        <a href="{{ route('guru.prakerin.index') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Dashboard</a>
        <a href="{{ route('guru.prakerin.locations') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">DU/DI Saya</a>
        <a href="{{ route('guru.prakerin.placements') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Penempatan Siswa</a>
        <a href="{{ route('guru.prakerin.izin') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-orange-600 text-white">Izin/Sakit/Libur</a>
        <a href="{{ route('guru.prakerin.recap.absensi') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Rekap Absensi</a>
        <a href="{{ route('guru.prakerin.recap.jurnal') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-gray-900 border border-white/10 text-gray-400 hover:text-white transition-colors">Rekap Jurnal</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif

    @if (! $period)
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-10 text-center">
            <p class="text-gray-400 text-sm">Tidak ada periode aktif.</p>
        </div>
    @elseif ($absences->isEmpty())
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-10 text-center">
            <p class="text-gray-400 text-sm">Belum ada pengajuan ketidakhadiran.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($absences as $abs)
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <p class="text-white font-semibold text-sm">{{ $abs->student->name }}</p>
                            <p class="text-gray-500 text-xs mt-0.5">
                                {{ $abs->placement->location->name }} &middot;
                                {{ $abs->absence_date->translatedFormat('l, d F Y') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg
                                {{ $abs->type === 'sakit' ? 'bg-red-500/10 text-red-400 border border-red-500/20' :
                                   ($abs->type === 'libur' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' :
                                   'bg-orange-500/10 text-orange-400 border border-orange-500/20') }}">
                                {{ $abs->type_label }}
                            </span>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg
                                {{ $abs->status === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' :
                                   ($abs->status === 'rejected' ? 'bg-red-500/10 text-red-400 border border-red-500/20' :
                                   'bg-amber-500/10 text-amber-400 border border-amber-500/20') }}">
                                {{ $abs->status_label }}
                            </span>
                        </div>
                    </div>

                    <p class="text-gray-300 text-sm mb-3">{{ $abs->reason }}</p>

                    @if ($abs->attachment_path)
                        <a href="{{ Storage::url($abs->attachment_path) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs text-blue-400 hover:text-blue-300 mb-3 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            Lihat lampiran
                        </a>
                    @endif

                    @if ($abs->notes)
                        <div class="p-3 bg-gray-800 rounded-xl mb-3">
                            <p class="text-gray-500 text-xs mb-0.5">Catatan:</p>
                            <p class="text-gray-300 text-sm">{{ $abs->notes }}</p>
                        </div>
                    @endif

                    @if ($abs->status === 'pending')
                        <div class="flex gap-2 mt-3 pt-3 border-t border-white/5">
                            {{-- Setujui --}}
                            <form action="{{ route('guru.prakerin.izin.approve', $abs) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit"
                                        class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl transition-colors"
                                        onclick="return confirm('Setujui ketidakhadiran {{ $abs->student->name }}?')">
                                    Setujui
                                </button>
                            </form>
                            {{-- Tolak --}}
                            <form action="{{ route('guru.prakerin.izin.reject', $abs) }}" method="POST" class="flex-1"
                                  onsubmit="return handleReject(this, event)">
                                @csrf
                                <input type="hidden" name="notes" id="reject-notes-{{ $abs->id }}">
                                <button type="submit"
                                        class="w-full py-2 bg-red-600/20 hover:bg-red-600/40 border border-red-500/20 text-red-400 text-xs font-semibold rounded-xl transition-colors">
                                    Tolak
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $absences->links() }}</div>
    @endif

    <script>
        function handleReject(form, e) {
            e.preventDefault();
            const notes = prompt('Alasan penolakan (wajib):');
            if (!notes || notes.trim() === '') return false;
            form.querySelector('[name="notes"]').value = notes;
            form.submit();
        }
    </script>
</x-simans-layout>
