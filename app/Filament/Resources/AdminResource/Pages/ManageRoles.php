<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Models\Admin;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ManageRoles extends Page
{
    protected static string $resource = \App\Filament\Resources\AdminResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Manage Roles';
    
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.resources.admin-role-management';

    public function getTitle(): string
    {
        return 'Manage Admin Roles';
    }

    public function mount(): void
    {
        if (!Auth::guard('admin')->check()) {
            redirect('/admin/login');
            return;
        }

        $user = Auth::guard('admin')->user();
        
        if (!$user || !$user->hasPermissionTo('admins.manage')) {
            Notification::make()
                ->title('Access Denied')
                ->body('Only Super Admins can manage roles and permissions.')
                ->danger()
                ->send();
            
            redirect('/admin');
        }
    }

    public function getViewData(): array
    {
        return [
            'admins' => Admin::with('roles')->get(),
            'roles' => \Spatie\Permission\Models\Role::all(),
            'currentUser' => Auth::guard('admin')->user(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}