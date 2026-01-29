<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class DateFilterWidget extends Widget
{
    protected static ?int $sort = 0;
    
    protected static ?string $heading = null;
    
    protected int | string | array $columnSpan = 'full';
    
    protected static string $view = 'filament.widgets.date-filter-simple';
    
    public function getDateFrom()
    {
        return request()->get('date_from', now()->startOfMonth()->toDateString());
    }
    
    public function getDateTo()
    {
        return request()->get('date_to', now()->toDateString());
    }
}