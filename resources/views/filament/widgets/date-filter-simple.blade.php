<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Date Filter</h3>
        <p class="text-sm text-gray-500">Filter dashboard statistics by date range</p>
    </div>
    
    <form method="GET" action="{{ route('filament.admin.pages.dashboard') }}" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
            <input type="date" name="date_from" value="{{ $this->getDateFrom() }}" 
                   class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                   max="{{ now()->toDateString() }}" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
            <input type="date" name="date_to" value="{{ $this->getDateTo() }}" 
                   class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                   max="{{ now()->toDateString() }}" />
        </div>
        <div class="flex gap-2">
            <button type="submit" 
                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700">
                Apply Filter
            </button>
            <a href="{{ route('filament.admin.pages.dashboard') }}" 
               class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Clear
            </a>
        </div>
    </form>
    
    @if(request()->get('date_from') || request()->get('date_to'))
        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
            <p class="text-sm text-blue-800">
                <strong>Active Filter:</strong> 
                @if(request()->get('date_from'))
                    From {{ \Carbon\Carbon::parse(request()->get('date_from'))->format('M j, Y') }}
                @endif
                @if(request()->get('date_to'))
                    to {{ \Carbon\Carbon::parse(request()->get('date_to'))->format('M j, Y') }}
                @endif
            </p>
        </div>
    @endif
</div>