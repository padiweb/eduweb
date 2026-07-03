{{-- resources/views/attendance/qr-print.blade.php --}}
{{-- Halaman cetak QR permanen per kelas --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Absensi — {{ $classroom->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            border: 3px solid #1D9E75;
            border-radius: 16px;
            padding: 32px 40px;
            text-align: center;
            max-width: 320px;
            width: 100%;
        }
        .school-name {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .class-name {
            font-size: 28px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 4px;
        }
        .major-name {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 24px;
        }
        .qr-wrapper {
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            display: inline-block;
        }
        .qr-wrapper svg {
            width: 220px;
            height: 220px;
            display: block;
        }
        .instruction {
            font-size: 14px;
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .sub-instruction {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.5;
        }
        .time-info {
            margin-top: 20px;
            padding: 12px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
        }
        .time-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .time-label { color: #6b7280; }
        .time-value { font-weight: 700; color: #111827; }
        .time-value.late { color: #d97706; }
        .brand {
            margin-top: 20px;
            font-size: 12px;
            color: #9ca3af;
        }
        .brand span { color: #1D9E75; font-weight: 700; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="card">
        <p class="school-name">{{ $classroom->academicYear->school->name ?? 'SiManS' }}</p>
        <h1 class="class-name">{{ $classroom->name }}</h1>
        <p class="major-name">{{ $classroom->major->name }}</p>

        <div class="qr-wrapper">
            {!! $qrImage !!}
        </div>

        <p class="instruction">Scan untuk Absensi</p>
        <p class="sub-instruction">
            Gunakan kamera HP, scan QR ini<br>
            setiap pagi sebelum pelajaran dimulai
        </p>

        <div class="time-info">
            <div class="time-row">
                <span class="time-label">Jam buka absensi</span>
                <span class="time-value">{{ substr($school->school_start_time, 0, 5) }} WIB</span>
            </div>
            <div class="time-row">
                <span class="time-label">Batas tepat waktu</span>
                <span class="time-value late">{{ substr($school->late_threshold_time, 0, 5) }} WIB</span>
            </div>
            <div class="time-row">
                <span class="time-label">Jam tutup absensi</span>
                <span class="time-value">{{ substr($school->attendance_close_time, 0, 5) }} WIB</span>
            </div>
        </div>

        <p class="brand">Powered by <span>SiManS</span></p>
    </div>

    <div class="no-print" style="position:fixed;bottom:20px;right:20px">
        <button onclick="window.print()"
                style="background:#1D9E75;color:#fff;border:none;padding:12px 24px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer">
            Cetak QR
        </button>
    </div>
</body>
</html>
