<x-simans-layout title="Monitor Absensi Guru">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Absensi Guru</h1>
            <p class="text-gray-500 text-sm mt-1">Monitor kehadiran guru harian</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.teacher-attendance.rewards') }}"
               class="text-sm text-gray-500 hover:text-blue-600 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-colors">
                Rekap Poin
            </a>
            <form method="POST" action="{{ route('admin.teacher-attendance.refresh-qr') }}">
                @csrf
                <button class="text-sm text-gray-500 hover:text-blue-600 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-colors">
                    Refresh QR
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter tanggal --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6">
        <form method="GET" class="flex items-center gap-3">
            <label class="text-sm text-gray-500">Tanggal:</label>
            <input type="date" name="date" value="{{ $date }}"
                   class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500 transition-colors">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-xl transition-colors">
                Lihat
            </button>
        </form>
    </div>

    @if($sessions->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-500 text-sm">Belum ada sesi absensi di tanggal ini.</p>
            <p class="text-gray-500 text-xs mt-1">Sesi dibuat otomatis oleh scheduler jam 06:00.</p>
        </div>
    @else
        @foreach($summary as $sum)
            @php $session = $sum['session']; @endphp
            <div class="mb-6 bg-white border border-gray-200 rounded-xl overflow-hidden">
                {{-- Header sesi --}}
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">
                            Sesi {{ $session->session_type === 'masuk' ? 'Masuk' : 'Pulang' }}
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ substr($session->open_time,0,5) }} — {{ substr($session->close_time,0,5) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-blue-600">{{ $sum['hadir'] }} hadir</span>
                        @if($sum['terlambat'] > 0) <span class="text-amber-600">{{ $sum['terlambat'] }} terlambat</span> @endif
                        @if($sum['izin'] > 0)      <span class="text-blue-400">{{ $sum['izin'] }} izin</span> @endif
                        @if($sum['sakit'] > 0)     <span class="text-blue-600">{{ $sum['sakit'] }} sakit</span> @endif
                        @if($sum['dinas'] > 0)     <span class="text-cyan-600">{{ $sum['dinas'] }} dinas</span> @endif
                        @if($sum['alfa'] > 0)      <span class="text-red-600">{{ $sum['alfa'] }} alfa</span> @endif
                    </div>
                </div>

                {{-- Daftar guru yang sudah absen --}}
                @if($session->attendances->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($session->attendances->sortBy('teacher.name') as $att)
                            @php
                                $colors = ['hadir'=>'emerald','terlambat'=>'amber','izin'=>'blue','sakit'=>'blue','dinas'=>'cyan','alfa'=>'red'];
                                $c = $colors[$att->status] ?? 'gray';
                            @endphp
                            <div class="flex items-center gap-3 px-5 py-3.5">
                                <div style="width:32px;height:32px;border-radius:50%;overflow:hidden;flex-shrink:0">
                                    <img src="{{ $att->teacher->avatarUrl }}" class="w-full h-full object-cover" alt="">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $att->teacher->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        @if($att->scanned_at) {{ $att->scanned_at->format('H:i') }} @endif
                                        @if($att->notes) &middot; {{ $att->notes }} @endif
                                        @if($att->is_manual_entry) &middot; <span class="text-amber-500">Manual</span> @endif
                                    </p>
                                </div>
                                @if($att->distance_meters !== null)
                                    <span class="text-xs text-gray-500 flex-shrink-0">{{ round($att->distance_meters) }}m</span>
                                @endif
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full border flex-shrink-0
                                    bg-{{ $c }}-500/10 text-{{ $c }}-400 border-{{ $c }}-500/20">
                                    {{ $att->statusLabel }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Guru yang belum absen --}}
                @if($sum['not_attended']->count() > 0)
                    <div class="px-5 py-3 border-t border-gray-200 bg-red-500/5">
                        <p class="text-xs font-semibold text-red-600 mb-2">Belum absen ({{ $sum['not_attended']->count() }}):</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($sum['not_attended'] as $t)
                                <span class="text-xs text-gray-500 bg-white border border-gray-200 px-2.5 py-1 rounded-lg">
                                    {{ $t->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Form input manual --}}
                <div class="px-5 py-4 border-t border-gray-200" x-data="{ showManual: false }">
                    <button type="button" @click="showManual=!showManual"
                            class="text-xs text-gray-500 hover:text-blue-600 transition-colors">
                        + Input Manual
                    </button>
                    <div x-show="showManual" x-cloak class="mt-3">
                        <form method="POST" action="{{ route('admin.teacher-attendance.manual') }}"
                              class="flex flex-wrap items-end gap-2">
                            @csrf
                            <input type="hidden" name="session_id" value="{{ $session->id }}">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Guru</label>
                                <select name="teacher_id" required
                                        class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                    <option value="">Pilih guru...</option>
                                    @foreach($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Status</label>
                                <select name="status" required
                                        class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                    <option value="hadir">Hadir</option>
                                    <option value="terlambat">Terlambat</option>
                                    <option value="izin">Izin</option>
                                    <option value="sakit">Sakit</option>
                                    <option value="dinas">Dinas</option>
                                    <option value="alfa">Alfa</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Keterangan</label>
                                <input type="text" name="notes" placeholder="Opsional"
                                       class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            </div>
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-xl transition-colors">
                                Simpan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

</x-simans-layout>
