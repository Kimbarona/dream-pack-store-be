<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Traits\HasModuleAccess;
use Illuminate\Support\Facades\Auth;

class GeneralSettings extends Page
{
    use HasModuleAccess;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Site Settings';

    protected static ?string $navigationLabel = 'General Settings';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.general-settings';

    protected ?string $heading = 'General Settings';

    public static function canView(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('settings.manage');
    }

    public function getTitle(): string
    {
        return 'General Settings';
    }
}