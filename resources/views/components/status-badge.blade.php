@props(['status'])

@php
$config = match($status) {
    'hadir'     => ['label' => 'Hadir',     'class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
    'terlambat' => ['label' => 'Terlambat', 'class' => 'bg-amber-500/10 text-amber-400 border-amber-500/20'],
    'izin'      => ['label' => 'Izin',      'class' => 'bg-blue-500/10 text-blue-400 border-blue-500/20'],
    'sakit'     => ['label' => 'Sakit',     'class' => 'bg-purple-500/10 text-purple-400 border-purple-500/20'],
    'alfa'      => ['label' => 'Alfa',      'class' => 'bg-red-500/10 text-red-400 border-red-500/20'],
    default     => ['label' => ucfirst($status), 'class' => 'bg-gray-500/10 text-gray-400 border-gray-500/20'],
};
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $config['class'] }}">
    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
    {{ $config['label'] }}
</span>