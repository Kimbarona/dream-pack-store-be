<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    @php
        $data = $this->getData();
    @endphp
    
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="flex-1">
                <h3 class="text-sm font-medium text-gray-500">Total Products</h3>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totalProducts']) }}</p>
                <p class="text-sm text-gray-600">{{ $data['activeProducts'] }} active</p>
            </div>
            <div class="ml-4">
                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
        <div class="flex items-center">
            <div class="flex-1">
                <h3 class="text-sm font-medium text-gray-500">Total Orders</h3>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totalOrders']) }}</p>
                <p class="text-sm text-gray-600">{{ $data['pendingOrders'] }} pending</p>
            </div>
            <div class="ml-4">
                <svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="flex-1">
                <h3 class="text-sm font-medium text-gray-500">Paid Orders</h3>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($data['paidOrders']) }}</p>
                <p class="text-sm text-gray-600">{{ $data['fromDate']->format('M j') }} - {{ $data['toDate']->format('M j') }}</p>
            </div>
            <div class="ml-4">
                <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-600">
        <div class="flex items-center">
            <div class="flex-1">
                <h3 class="text-sm font-medium text-gray-500">Total Revenue</h3>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($data['totalRevenue'], 2) }}</p>
                <p class="text-sm text-gray-600">In selected date range</p>
            </div>
            <div class="ml-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>