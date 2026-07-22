<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Guru — {{ $school->name ?? 'SiManS' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #111827; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm text-center">

        @if($success)
            {{-- Berhasil --}}
            @php
                $isLate = ($status ?? '') === 'terlambat';
                $color  = $isLate ? 'amber' : 'emerald';
            @endphp
            <div class="w-20 h-20 rounded-full bg-{{ $color }}-500/10 border-2 border-{{ $color }}-500/30 flex items-center justify-center mx-auto mb-5">
                @if($isLate)
                    <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                @else
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @endif
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $message }}</h1>
            <p class="text-gray-500 text-sm mb-1">
                {{ $session?->session_type === 'masuk' ? 'Absen Masuk' : 'Absen Pulang' }}
            </p>
            <p class="text-gray-500 text-sm mb-6">
                Tercatat pukul {{ $attendance?->scanned_at?->format('H:i:s') }} WIB
            </p>
            @if($isLate)
                <div class="bg-amber-900/20 border border-amber-500/20 rounded-xl px-4 py-3 text-sm text-amber-300 mb-5">
                    Kamu tercatat terlambat. Harap lebih tepat waktu besok.
                </div>
            @else
                <div class="bg-emerald-900/20 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-700 mb-5">
                    +1 poin reward telah ditambahkan ke akunmu!
                </div>
            @endif
        @else
            {{-- Gagal --}}
            <div class="w-20 h-20 rounded-full bg-red-500/10 border-2 border-red-500/30 flex items-center justify-center mx-auto mb-5">
                <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Absensi Gagal</h1>
            <p class="text-gray-500 text-sm mb-6">{{ $message }}</p>
        @endif

        <a href="{{ route('guru.teacher-attendance.index') }}"
           class="inline-block w-full bg-white hover:bg-gray-50 border border-gray-200 text-gray-900 font-semibold py-3 rounded-xl transition-colors">
            Kembali ke Halaman Absensi
        </a>
    </div>
</body>
</html>
