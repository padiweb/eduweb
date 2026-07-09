<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Absensi Guru — {{ $school->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #111; }
        .page { max-width: 400px; margin: 0 auto; padding: 24px; text-align: center; }
        .school-name { font-size: 16px; font-weight: 700; color: #111; margin-bottom: 4px; }
        .subtitle { font-size: 13px; color: #555; margin-bottom: 20px; }
        .qr-box { border: 3px solid #111; border-radius: 16px; padding: 20px; display: inline-block; margin-bottom: 16px; }
        canvas { display: block; }
        .token { font-size: 11px; color: #999; margin-bottom: 12px; font-family: monospace; }
        .instructions { background: #f5f5f5; border-radius: 10px; padding: 14px; text-align: left; margin-bottom: 16px; }
        .instructions p { font-size: 12px; color: #444; margin-bottom: 6px; line-height: 1.5; }
        .instructions p:last-child { margin-bottom: 0; }
        .schedule { background: #111; color: #fff; border-radius: 10px; padding: 14px; margin-bottom: 16px; }
        .schedule-title { font-size: 12px; font-weight: 600; margin-bottom: 10px; color: #aaa; text-transform: uppercase; letter-spacing: 0.05em; }
        .schedule-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px; }
        .schedule-row:last-child { margin-bottom: 0; }
        .schedule-label { color: #aaa; }
        .schedule-time { color: #fff; font-weight: 600; }
        .btn-refresh { display: inline-block; margin-top: 8px; padding: 8px 20px; background: #111; color: #fff; border-radius: 8px; font-size: 12px; text-decoration: none; border: none; cursor: pointer; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
        }
    </style>
</head>
<body>
    <div class="page">
        @if($school->logo_path)
            <img src="{{ asset('storage/'.$school->logo_path) }}" alt="Logo" style="width:60px;height:60px;object-fit:contain;margin:0 auto 12px;">
        @endif
        <p class="school-name">{{ $school->name }}</p>
        <p class="subtitle">QR Absensi Guru — Scan untuk Hadir</p>

        <div class="qr-box">
            <canvas id="qr-canvas"></canvas>
        </div>

        <p class="token">Token: {{ substr($token, 0, 8) }}...{{ substr($token, -4) }}</p>

        <div class="schedule">
            <p class="schedule-title">Jadwal Absensi Hari Ini</p>
            <div class="schedule-row">
                <span class="schedule-label">Buka Absen Masuk</span>
                <span class="schedule-time">{{ substr($school->teacher_checkin_open ?? '06:30', 0, 5) }}</span>
            </div>
            <div class="schedule-row">
                <span class="schedule-label">Batas Terlambat</span>
                <span class="schedule-time">{{ substr($school->teacher_checkin_late ?? '07:15', 0, 5) }}</span>
            </div>
            <div class="schedule-row">
                <span class="schedule-label">Tutup Absen Masuk</span>
                <span class="schedule-time">{{ substr($school->teacher_checkin_close ?? '08:00', 0, 5) }}</span>
            </div>
            <div class="schedule-row">
                <span class="schedule-label">Buka Absen Pulang</span>
                <span class="schedule-time">{{ substr($school->teacher_checkout_open ?? '14:00', 0, 5) }}</span>
            </div>
            <div class="schedule-row">
                <span class="schedule-label">Tutup Absen Pulang</span>
                <span class="schedule-time">{{ substr($school->teacher_checkout_close ?? '16:00', 0, 5) }}</span>
            </div>
        </div>

        <div class="instructions">
            <p>1. Buka aplikasi SiManS di HP</p>
            <p>2. Masuk ke menu <strong>Absensi Saya</strong></p>
            <p>3. Klik tombol <strong>Scan QR Absen</strong></p>
            <p>4. Arahkan kamera ke QR di atas</p>
            <p>5. Pastikan GPS aktif dan berada di area sekolah</p>
        </div>

        <div class="no-print" style="margin-top: 8px; display: flex; gap: 8px; justify-content: center;">
            <button onclick="window.print()" class="btn-refresh">Cetak / Simpan PDF</button>
            <form method="POST" action="{{ route('admin.teacher-attendance.refresh-qr') }}" style="display:inline">
                @csrf
                <button type="submit" class="btn-refresh" style="background:#555"
                        onclick="return confirm('Refresh QR akan membuat QR lama tidak berlaku. Lanjutkan?')">
                    Refresh QR
                </button>
            </form>
        </div>

        <p style="font-size:11px;color:#ccc;margin-top:16px;">{{ now()->translatedFormat('d F Y') }}</p>
    </div>

    <script>
    QRCode.toCanvas(
        document.getElementById('qr-canvas'),
        '{{ $token }}',
        { width: 260, margin: 0, color: { dark: '#000000', light: '#ffffff' } },
        function(err) { if (err) console.error(err); }
    );
    </script>
</body>
</html>
