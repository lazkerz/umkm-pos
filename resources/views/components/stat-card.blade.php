@props(['label', 'tone' => 'default', 'icon' => null])

@php
$toneClasses = [
    'default' => 'text-slate-900',
    'positive' => 'text-emerald-600',
    'negative' => 'text-rose-600',
][$tone] ?? 'text-slate-900';
@endphp

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <div class="flex items-start justify-between">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $label }}</p>
        @if($icon)
            <span class="text-slate-300"><x-icon :name="$icon" class="w-4 h-4" /></span>
        @endif
    </div>
    <p class="text-2xl font-bold mt-1.5 {{ $toneClasses }}">{{ $slot }}</p>
</div>
