<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi {{ $bill->paymentType->name }} - {{ $bill->student->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            width: 72mm;          /* pas untuk printer 80mm, margin ~4mm kiri kanan */
            margin: 0 auto;
            padding: 4mm;
            color: #000;
            background: #fff;
        }

        .center  { text-align: center; }
        .right   { text-align: right; }
        .bold    { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 3mm 0; }
        .divider-solid { border-top: 1px solid #000; margin: 3mm 0; }
        .row     { display: flex; justify-content: space-between; align-items: flex-start; margin: 1mm 0; }
        .row .label { flex: 1; color: #333; }
        .row .val   { text-align: right; font-weight: 600; }
        .row-total  { display: flex; justify-content: space-between; align-items: center; margin: 1.5mm 0; }
        .row-total .label { font-weight: bold; font-size: 12px; }
        .row-total .val   { font-weight: bold; font-size: 13px; }
        .section-title { font-weight: bold; font-size: 10px; text-transform: uppercase; color: #555; margin: 2mm 0 1mm; }
        .stamp { border: 2px solid #000; padding: 2mm 4mm; display: inline-block; margin-top: 2mm; font-weight: bold; font-size: 13px; letter-spacing: 2px; }
        .footer-text { font-size: 9px; color: #666; margin-top: 1mm; }

        @media print {
            body { margin: 0; padding: 2mm; }
            .no-print { display: none !important; }
            @page { margin: 0; size: 80mm auto; }
        }
    </style>
</head>
<body>

    {{-- Header sekolah --}}
    <div class="center" style="margin-bottom:2mm">
        @if($school->logo_path)
            <img src="{{ Storage::url($school->logo_path) }}" alt="" style="height:10mm;object-fit:contain;display:block;margin:0 auto 1mm">
        @endif
        <div class="bold" style="font-size:13px">{{ $school->name }}</div>
        @if($school->address)
            <div style="font-size:9px;color:#555">{{ $school->address }}</div>
        @endif
        @if($school->phone)
            <div style="font-size:9px;color:#555">Telp: {{ $school->phone }}</div>
        @endif
    </div>

    <div class="divider-solid"></div>

    <div class="center bold" style="font-size:12px;margin:1mm 0">KWITANSI PEMBAYARAN</div>
    <div class="center" style="font-size:9px;color:#555">No: {{ $bill->transactions->last()?->reference_number ?? '-' }}</div>

    <div class="divider"></div>

    {{-- Data siswa --}}
    <div class="section-title">Data Siswa</div>
    <div class="row">
        <span class="label">Nama</span>
        <span class="val">{{ $bill->student->name }}</span>
    </div>
    <div class="row">
        <span class="label">NIS</span>
        <span class="val">{{ $bill->student->nis ?? '-' }}</span>
    </div>

    <div class="divider"></div>

    {{-- Detail tagihan --}}
    <div class="section-title">Rincian Pembayaran</div>
    <div class="row">
        <span class="label">Jenis</span>
        <span class="val">{{ $bill->paymentType->name }}</span>
    </div>
    <div class="row">
        <span class="label">Periode</span>
        <span class="val">{{ $bill->period_label }}</span>
    </div>
    <div class="row">
        <span class="label">Th. Ajaran</span>
        <span class="val">{{ $bill->academicYear->name }} S{{ $bill->academicYear->semester }}</span>
    </div>

    @if($bill->amount_discount > 0)
    <div class="row">
        <span class="label">Tarif dasar</span>
        <span class="val">Rp {{ number_format($bill->amount_base, 0, ',', '.') }}</span>
    </div>
    <div class="row">
        <span class="label">Diskon</span>
        <span class="val">- Rp {{ number_format($bill->amount_discount, 0, ',', '.') }}</span>
    </div>
    @endif

    <div class="divider"></div>

    {{-- Transaksi --}}
    <div class="section-title">Riwayat Bayar</div>
    @foreach($bill->transactions as $trx)
    <div class="row">
        <span class="label">{{ $trx->created_at->format('d/m/Y') }}
        {{ $trx->channel === 'scholarship' ? '(Beasiswa)' : '(Tunai)' }}</span>
        <span class="val">Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
    </div>
    @endforeach

    <div class="divider"></div>

    <div class="row-total">
        <span class="label">Total Tagihan</span>
        <span class="val">Rp {{ number_format($bill->amount_billed, 0, ',', '.') }}</span>
    </div>
    <div class="row-total">
        <span class="label">Dibayar</span>
        <span class="val">Rp {{ number_format($bill->amount_paid, 0, ',', '.') }}</span>
    </div>
    @if($bill->amount_remaining > 0)
    <div class="row-total">
        <span class="label" style="color:#c00">Sisa</span>
        <span class="val" style="color:#c00">Rp {{ number_format($bill->amount_remaining, 0, ',', '.') }}</span>
    </div>
    @endif

    <div class="divider-solid"></div>

    {{-- Status --}}
    <div class="center" style="margin:2mm 0">
        @if($bill->status === 'paid')
            <span class="stamp">L U N A S</span>
        @elseif($bill->status === 'waived')
            <span class="stamp">DIBEBASKAN</span>
        @else
            <div style="font-size:10px">Sisa: <strong>Rp {{ number_format($bill->amount_remaining, 0, ',', '.') }}</strong></div>
        @endif
    </div>

    <div class="divider"></div>

    {{-- TTD --}}
    <div style="display:flex;justify-content:space-between;margin-top:3mm">
        <div class="center" style="width:45%">
            <div style="font-size:9px">Wali Murid/Siswa</div>
            <div style="height:12mm"></div>
            <div style="border-top:1px solid #000;font-size:9px;padding-top:1mm">(________________)</div>
        </div>
        <div class="center" style="width:45%">
            <div style="font-size:9px">Bendahara</div>
            <div style="height:12mm"></div>
            <div style="border-top:1px solid #000;font-size:9px;padding-top:1mm">{{ auth()->user()->name }}</div>
        </div>
    </div>

    <div class="divider"></div>

    <div class="center footer-text">
        Dicetak {{ now()->format('d/m/Y H:i') }} · EduWeb by Padiweb
    </div>

    {{-- Tombol cetak (tidak ikut print) --}}
    <div class="no-print" style="margin-top:8mm;text-align:center">
        <button onclick="window.print()"
            style="background:#10b981;color:#fff;border:none;padding:8px 24px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:600">
            Cetak Kwitansi
        </button>
        <button onclick="window.close()"
            style="background:#374151;color:#fff;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;margin-left:8px">
            Tutup
        </button>
    </div>

    <script>
    // Auto print jika dibuka dari link langsung
    // window.onload = function() { window.print(); }
    </script>

</body>
</html>
