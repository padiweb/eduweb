@props(['label', 'value', 'color' => 'blue', 'icon' => '', 'sub' => null, 'trend' => null])

@php
$colors = [
    'blue'    => ['val'=>'#1e40af','icon_bg'=>'#eff6ff','icon_color'=>'#3b82f6','border'=>'#3b82f6'],
    'emerald' => ['val'=>'#065f46','icon_bg'=>'#ecfdf5','icon_color'=>'#10b981','border'=>'#10b981'],
    'amber'   => ['val'=>'#92400e','icon_bg'=>'#fffbeb','icon_color'=>'#f59e0b','border'=>'#f59e0b'],
    'red'     => ['val'=>'#991b1b','icon_bg'=>'#fff1f2','icon_color'=>'#ef4444','border'=>'#ef4444'],
    'purple'  => ['val'=>'#1e40af','icon_bg'=>'#eff6ff','icon_color'=>'#3b82f6','border'=>'#3b82f6'],
    'orange'  => ['val'=>'#9a3412','icon_bg'=>'#fff7ed','icon_color'=>'#f97316','border'=>'#f97316'],
    'gray'    => ['val'=>'#334155','icon_bg'=>'#f8fafc','icon_color'=>'#64748b','border'=>'#cbd5e1'],
];
$c = $colors[$color] ?? $colors['blue'];
@endphp

<div style="background:#fff;border:1px solid #e2e8f0;border-top:3px solid {{ $c['border'] }};border-radius:14px;padding:18px 20px;box-shadow:0 1px 4px rgba(15,23,42,0.06);position:relative;overflow:hidden;transition:box-shadow .2s"
     onmouseover="this.style.boxShadow='0 4px 16px rgba(15,23,42,0.10)'"
     onmouseout="this.style.boxShadow='0 1px 4px rgba(15,23,42,0.06)'">
    <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:{{ $c['icon_bg'] }};border-radius:50%;opacity:.5"></div>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px">
        <p style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.07em;margin:0">{{ $label }}</p>
        @if($trend)
            <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;background:{{ str_starts_with($trend,'+') ? '#ecfdf5' : '#fff1f2' }};color:{{ str_starts_with($trend,'+') ? '#065f46' : '#991b1b' }}">{{ $trend }}</span>
        @endif
    </div>
    <p style="font-size:30px;font-weight:800;color:{{ $c['val'] }};letter-spacing:-1px;line-height:1;margin:0">{{ $value }}</p>
    @if($sub)
        <p style="font-size:11.5px;color:#94a3b8;margin:6px 0 0">{{ $sub }}</p>
    @endif
</div>
