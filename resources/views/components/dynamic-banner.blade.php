@php
use App\Models\Banner;

/** @var \Illuminate\View\Component $component */
/** @var string|null $title */
/** @var string|null $subtitle */
/** @var string $image */
/** @var string|null $mobileImage */
/** @var string|null $link */
/** @var bool $isActive */
/** @var bool $showTitle = true */
/** @var bool $showSubtitle = true */
/** @var string $size = 'full' */
?>

@if ($isActive)
    @if($link)
        <a href="{{ $link }}" class="block group relative overflow-hidden rounded-lg">
    @else
        <div class="group relative overflow-hidden rounded-lg">
    @endif

    {{-- Mobile Image --}}
    <div class="lg:hidden">
        <img 
            src="{{ $mobileImage ?: $image }}" 
            alt="{{ $title ?? 'Banner' }}"
            class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105"
        >
    </div>

    {{-- Desktop Image --}}
    <div class="hidden lg:block">
        <img 
            src="{{ $image }}" 
            alt="{{ $title ?? 'Banner' }}"
            class="w-full {{ $size === 'full' ? 'h-64 md:h-80' : 'h-48 md:h-64' }} object-cover transition-transform duration-300 group-hover:scale-105"
        >
    </div>

    {{-- Content Overlay --}}
    @if ($showTitle || $showSubtitle)
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-6">
            <div class="text-white">
                @if ($showTitle && $title)
                    <h2 class="text-2xl md:text-3xl font-bold mb-2">{{ $title }}</h2>
                @endif
                
                @if ($showSubtitle && $subtitle)
                    <p class="text-sm md:text-base opacity-90">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    @endif

    @if($link)
        </a>
    @else
        </div>
    @endif
@endif