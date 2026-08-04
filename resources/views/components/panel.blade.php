@props(['title', 'icon' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-sm p-5']) }}>
    <div class="flex items-center gap-2 mb-3">
        @if($icon)
            <span class="text-indigo-500"><x-icon :name="$icon" class="w-4 h-4" /></span>
        @endif
        <h2 class="font-semibold text-sm text-slate-800">{{ $title }}</h2>
    </div>

    {{ $slot }}
</div>
