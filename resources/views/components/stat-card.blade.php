@props(['label', 'value', 'color' => 'emerald', 'icon' => '', 'sub' => null, 'trend' => null])

@php
$colors = [
    'emerald' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'border' => 'border-t-emerald-500', 'icon_bg' => 'bg-emerald-500/10'],
    'blue'    => ['bg' => 'bg-blue-500/10',    'text' => 'text-blue-400',    'border' => 'border-t-blue-500',    'icon_bg' => 'bg-blue-500/10'],
    'amber'   => ['bg' => 'bg-amber-500/10',   'text' => 'text-amber-400',   'border' => 'border-t-amber-500',   'icon_bg' => 'bg-amber-500/10'],
    'red'     => ['bg' => 'bg-red-500/10',     'text' => 'text-red-400',     'border' => 'border-t-red-500',     'icon_bg' => 'bg-red-500/10'],
    'purple'  => ['bg' => 'bg-purple-500/10',  'text' => 'text-purple-400',  'border' => 'border-t-purple-500',  'icon_bg' => 'bg-purple-500/10'],
    'gray'    => ['bg' => 'bg-gray-500/10',    'text' => 'text-gray-400',    'border' => 'border-t-gray-500',    'icon_bg' => 'bg-gray-500/10'],
];
$c = $colors[$color] ?? $colors['emerald'];
@endphp

<div class="bg-gray-900 border border-white/5 border-t-2 {{ $c['border'] }} rounded-xl p-5">
    <div class="flex items-start justify-between mb-3">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">{{ $label }}</p>
        @if($trend)
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                {{ str_starts_with($trend, '+') ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                {{ $trend }}
            </span>
        @endif
    </div>
    <p class="text-3xl font-bold text-white tracking-tight">{{ $value }}</p>
    @if($sub)
        <p class="text-xs text-gray-500 mt-1">{{ $sub }}</p>
    @endif
</div>