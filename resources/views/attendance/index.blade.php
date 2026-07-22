<x-simans-layout title="Absensi Siswa">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Absensi Siswa</h1>
            <p class="text-gray-500 text-sm mt-1">
                Sesi dibuat otomatis setiap pagi · Guru cukup validasi roll call
            </p>
        </div>
        <div class="text-right">
            <p class="text-gray-900 font-semibold">{{ now()->translatedFormat('l, d F Y') }}</p>
            <p class="text-gray-500 text-xs mt-0.5">
                Jam buka: {{ substr(auth()->user()->school->school_start_time, 0, 5) }} —
                Batas: {{ substr(auth()->user()->school->late_threshold_time, 0, 5) }} —
                Tutup: {{ substr(auth()->user()->school->attendance_close_time, 0, 5) }}
            </p>
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

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($classrooms as $classroom)
            @php
                $session    = $todaySessions->get($classroom->id);
                $hasSession = $session !== null;
                $attended   = $hasSession ? $session->attendances->count() : 0;
                $total      = $classroom->students->count();
                $rate       = $total > 0 ? round(($attended / $total) * 100) : 0;
            @endphp

            <div class="bg-white border {{ $hasSession && !$session->is_closed ? 'border-blue-200' : 'border-gray-200' }} rounded-xl overflow-hidden">

                {{-- Header --}}
                <div class="flex items-start justify-between p-5 pb-4">
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">{{ $classroom->name }}</h3>
                        <p class="text-gray-500 text-sm">{{ $classroom->major->name }}</p>
                        <p class="text-gray-500 text-xs mt-1">{{ $total }} siswa</p>
                    </div>

                    @if($hasSession)
                        <span class="flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $session->is_closed
                                ? 'bg-white text-gray-500 border border-gray-200'
                                : 'bg-blue-600/10 text-blue-600 border border-blue-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $session->is_closed ? 'bg-gray-500' : 'bg-blue-500 animate-pulse' }}"></span>
                            {{ $session->is_closed ? 'Ditutup' : 'Aktif' }}
                        </span>
                    @else
                        <span class="text-xs text-gray-500 bg-white border border-gray-200 px-2.5 py-1 rounded-full">
                            Belum ada sesi
                        </span>
                    @endif
                </div>

                {{-- Progress --}}
                @if($hasSession && $total > 0)
                    <div class="px-5 mb-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                            <span>{{ $attended }} / {{ $total }} hadir</span>
                            <span class="{{ $rate >= 80 ? 'text-blue-600' : ($rate >= 60 ? 'text-amber-400' : 'text-red-400') }}">
                                {{ $rate }}%
                            </span>
                        </div>
                        <div class="h-1.5 bg-white rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full" style="width: {{ $rate }}%"></div>
                        </div>
                        <div class="flex gap-3 mt-2">
                            @if($session->attendances->where('status', 'hadir')->count() > 0)
                                <span class="text-xs text-blue-600">{{ $session->attendances->where('status', 'hadir')->count() }} hadir</span>
                            @endif
                            @if($session->attendances->where('status', 'terlambat')->count() > 0)
                                <span class="text-xs text-amber-400">{{ $session->attendances->where('status', 'terlambat')->count() }} terlambat</span>
                            @endif
                            @if($session->attendances->where('status', 'alfa')->count() > 0)
                                <span class="text-xs text-red-400">{{ $session->attendances->where('status', 'alfa')->count() }} alfa</span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Aksi — hanya Lihat Detail + Cetak QR, tidak ada refresh QR --}}
                <div class="px-5 pb-5 flex gap-2">
                    @if($hasSession)
                        <a href="{{ route('guru.attendance.show', $session->id) }}"
                           class="flex-1 text-center text-sm font-semibold py-2 rounded-xl bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 transition-colors">
                            Lihat Detail
                        </a>
                    @else
                        <form method="POST" action="{{ route('guru.attendance.open') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                            <button type="submit"
                                    class="w-full text-sm font-semibold py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition-colors">
                                + Buka Manual
                            </button>
                        </form>
                    @endif

                    {{-- Cetak QR permanen — tetap ada untuk guru --}}
                    @if($classroom->slug)
                        <a href="{{ route('guru.attendance.class.print-qr', $classroom->id) }}"
                           target="_blank"
                           class="px-3 py-2 text-sm font-medium rounded-xl bg-white hover:bg-gray-50 text-gray-500 hover:text-gray-900 border border-gray-200 transition-colors"
                           title="Cetak QR Permanen">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                            </svg>
                        </a>
                    @endif

                    {{-- TIDAK ADA tombol refresh QR di sini --}}
                    {{-- Perbarui QR hanya bisa dilakukan oleh admin --}}
                    {{-- di menu Admin > Kelola QR Kelas --}}
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white border border-gray-200 rounded-xl p-12 text-center">
                <p class="text-gray-500">Belum ada kelas aktif di tahun ajaran ini.</p>
                <p class="text-gray-500 text-sm mt-1">Hubungi admin untuk menambahkan kelas.</p>
            </div>
        @endforelse
    </div>

</x-simans-layout>