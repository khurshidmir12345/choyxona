@props(['icon' => 'box', 'title' => 'Hozircha bo\'sh', 'description' => null])

<div class="flex flex-col items-center justify-center px-6 py-14 text-center">
    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-ink-100 text-ink-400">
        <x-icon :name="$icon" class="h-6 w-6"/>
    </div>
    <p class="text-sm font-semibold text-ink-800">{{ $title }}</p>
    @if($description)
        <p class="mt-1 max-w-sm text-sm text-ink-500">{{ $description }}</p>
    @endif
    @if(trim($slot) !== '')
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
