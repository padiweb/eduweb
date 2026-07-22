@props(['status'])

@php
$config = match($status) {
    'hadir'     => ['label'=>'Hadir',     'bg'=>'#ecfdf5','color'=>'#065f46','dot'=>'#10b981'],
    'terlambat' => ['label'=>'Terlambat', 'bg'=>'#fffbeb','color'=>'#92400e','dot'=>'#f59e0b'],
    'izin'      => ['label'=>'Izin',      'bg'=>'#eff6ff','color'=>'#1e40af','dot'=>'#3b82f6'],
    'sakit'     => ['label'=>'Sakit',     'bg'=>'#f5f3ff','color'=>'#4c1d95','dot'=>'#7c3aed'],
    'alfa'      => ['label'=>'Alfa',      'bg'=>'#fff1f2','color'=>'#991b1b','dot'=>'#ef4444'],
    default     => ['label'=>ucfirst($status),'bg'=>'#f8fafc','color'=>'#475569','dot'=>'#94a3b8'],
};
@endphp

<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:{{ $config['bg'] }};color:{{ $config['color'] }}">
    <span style="width:6px;height:6px;border-radius:50%;background:{{ $config['dot'] }};flex-shrink:0"></span>
    {{ $config['label'] }}
</span>
