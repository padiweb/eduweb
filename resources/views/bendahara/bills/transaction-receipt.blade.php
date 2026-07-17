<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pembayaran</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            width: 72mm;
            margin: 0 auto;
            padding: 3mm;
            color: #000;
            background: #fff;
        }
        .center { text-align:center; }
        .right  { text-align:right; }
        .bold   { font-weight:bold; }
        .line   { border-top:1px dashed #000; margin:2.5mm 0; }
        .line-solid { border-top:1px solid #000; margin:2.5mm 0; }
        .row    { display:flex; justify-content:space-between; margin:1mm 0; }
        .row .lbl { color:#555; }
        .row .val { font-weight:600; }
        .big    { font-size:14px; font-weight:bold; }
        .stamp  { border:2px solid #000; display:inline-block; padding:1.5mm 4mm; font-size:13px; font-weight:bold; letter-spacing:2px; margin:2mm 0; }
        @media print {
            body { padding:1mm; }
            .no-print { display:none !important; }
            @page { margin:0; size:80mm auto; }
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="center">
        @if($school->logo_path)
            <img src="{{ Storage::url($school->logo_path) }}" alt="" style="height:9mm;object-fit:contain;display:block;margin:0 auto 1.5mm">
        @endif
        <div class="bold" style="font-size:12px">{{ $school->name }}</div>
        @if($school->address)
            <div style="font-size:9px;color:#555">{{ $school->address }}</div>
        @endif
    </div>

    <div class="line-solid"></div>
    <div class="center bold" style="font-size:11px">BUKTI PEMBAYARAN</div>
    <div class="center" style="font-size:9px;color:#555">{{ $transaction->reference_number }}</div>
    <div class="center" style="font-size:9px;color:#555">{{ $transaction->created_at->format('d/m/Y H:i') }}</div>
    <div class="line"></div>

    {{-- Data siswa --}}
    <div class="row"><span class="lbl">Nama</span><span class="val">{{ $bill->student->name }}</span></div>
    <div class="row"><span class="lbl">NIS</span><span class="val">{{ $bill->student->nis ?? '-' }}</span></div>

    <div class="line"></div>

    {{-- Detail tagihan --}}
    <div class="row"><span class="lbl">Jenis</span><span class="val">{{ $bill->paymentType->name }}</span></div>
    <div class="row"><span class="lbl">Periode</span><span class="val">{{ $bill->period_label }}</span></div>
    <div class="row"><span class="lbl">Th. Ajaran</span><span class="val">{{ $bill->academicYear->name }} S{{ $bill->academicYear->semester }}</span></div>

    <div class="line"></div>

    {{-- Pembayaran ini --}}
    <div class="row">
        <span class="lbl">Cara bayar</span>
        <span class="val">{{ $transaction->channel === 'scholarship' ? 'Beasiswa' : 'Tunai' }}</span>
    </div>
    @if($transaction->cashier_notes)
    <div class="row"><span class="lbl">Catatan</span><span class="val">{{ $transaction->cashier_notes }}</span></div>
    @endif

    <div class="line-solid"></div>
    <div class="row">
        <span class="bold" style="font-size:12px">Dibayar sekarang</span>
        <span class="big">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
    </div>
    <div class="line"></div>

    {{-- Ringkasan tagihan --}}
    <div class="row"><span class="lbl">Total tagihan</span><span>Rp {{ number_format($bill->amount_billed, 0, ',', '.') }}</span></div>
    <div class="row"><span class="lbl">Sudah dibayar</span><span>Rp {{ number_format($bill->amount_paid, 0, ',', '.') }}</span></div>
    @if($bill->amount_remaining > 0)
    <div class="row"><span class="lbl" style="color:#c00">Sisa tagihan</span><span style="color:#c00;font-weight:600">Rp {{ number_format($bill->amount_remaining, 0, ',', '.') }}</span></div>
    @endif

    <div class="line-solid"></div>

    {{-- Status --}}
    <div class="center" style="margin:2mm 0">
        @if($bill->status === 'paid')
            <span class="stamp">L U N A S</span>
        @elseif($bill->status === 'waived')
            <span class="stamp">DIBEBASKAN</span>
        @else
            <div style="font-size:10px">Pembayaran ke-{{ $paymentNumber }} dari total cicilan</div>
            <div style="font-size:9px;color:#555">Sisa: Rp {{ number_format($bill->amount_remaining, 0, ',', '.') }}</div>
        @endif
    </div>

    <div class="line"></div>

    {{-- TTD --}}
    <div style="display:flex;justify-content:space-between;margin-top:3mm">
        <div class="center" style="width:45%">
            <div style="font-size:9px">Penerima</div>
            <div style="height:10mm"></div>
            <div style="border-top:1px solid #000;font-size:9px;padding-top:0.5mm">(_____________)</div>
        </div>
        <div class="center" style="width:45%">
            <div style="font-size:9px">Bendahara</div>
            <div style="height:10mm"></div>
            <div style="border-top:1px solid #000;font-size:9px;padding-top:0.5mm">{{ auth()->user()->name }}</div>
        </div>
    </div>

    <div class="line"></div>
    <div class="center" style="font-size:9px;color:#999">
        Dicetak {{ now()->format('d/m/Y H:i') }} · EduWeb by Padiweb
    </div>

    <div class="no-print" style="margin-top:8mm;text-align:center">
        <button onclick="window.print()"
            style="background:#059669;color:#fff;border:none;padding:7px 20px;border-radius:6px;font-size:12px;cursor:pointer;font-weight:600">
            Cetak
        </button>
        <button onclick="window.close()"
            style="background:#374151;color:#fff;border:none;padding:7px 14px;border-radius:6px;font-size:12px;cursor:pointer;margin-left:6px">
            Tutup
        </button>
    </div>

</body>
</html>
