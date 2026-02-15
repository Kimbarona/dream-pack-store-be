<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banner Preview - {{ $banner->title ?? 'Untitled' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-4">
        <!-- Header -->
        <div class="bg-white shadow-sm rounded-lg mb-6 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Banner Preview</h1>
                    <p class="text-gray-600 mt-1">This is how your banner will appear on the frontend</p>
                </div>
                <div class="flex space-x-2">
                    @if($banner->link_url)
                        <a href="{{ $banner->link_url }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Open Link
                        </a>
                    @endif
                    <button onclick="window.close()" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Banner Preview -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="relative">
                @if($banner->link_url)
                    <a href="{{ $banner->link_url }}" target="_blank" class="block">
                @endif
                
                <!-- Desktop Image -->
                <div class="hidden lg:block">
                    @if($banner->image_url)
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?? 'Banner' }}" class="w-full h-auto">
                    @else
                        <div class="bg-gray-200 h-96 flex items-center justify-center">
                            <p class="text-gray-500">No desktop image uploaded</p>
                        </div>
                    @endif
                </div>

                <!-- Mobile Image -->
                <div class="lg:hidden">
                    @if($banner->image_url)
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?? 'Banner' }}" class="w-full h-auto">
                    @else
                        <div class="bg-gray-200 h-48 flex items-center justify-center">
                            <p class="text-gray-500">No mobile image uploaded</p>
                        </div>
                    @endif
                </div>

                <!-- Text Overlay -->
                @if($banner->title || $banner->subtitle)
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end">
                        <div class="p-8 text-white">
                            @if($banner->title)
                                <h2 class="text-3xl lg:text-5xl font-bold mb-2">{{ $banner->title }}</h2>
                            @endif
                            @if($banner->subtitle)
                                <p class="text-lg lg:text-xl opacity-90">{{ $banner->subtitle }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if($banner->link_url)
                    </a>
                @endif
            </div>
        </div>

        <!-- Banner Details -->
        <div class="bg-white shadow-lg rounded-lg p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Banner Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $banner->title ?? 'None' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Subtitle</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $banner->subtitle ?? 'None' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Link URL</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $banner->link_url ?? 'None' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $banner->sort_order }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <span class="inline-flex mt-1 px-2 py-1 text-xs font-semibold rounded-full {{ $banner->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $banner->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Currently Active</label>
                    <span class="inline-flex mt-1 px-2 py-1 text-xs font-semibold rounded-full {{ $banner->isCurrentlyActive() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $banner->isCurrentlyActive() ? 'Yes' : 'No' }}
                    </span>
                </div>
                @if($banner->starts_at)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Starts At</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $banner->starts_at->format('M j, Y g:i A') }}</p>
                </div>
                @endif
                @if($banner->ends_at)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ends At</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $banner->ends_at->format('M j, Y g:i A') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>