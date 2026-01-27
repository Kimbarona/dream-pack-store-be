<?php

namespace App\Filament\Pages;

use App\Models\Admin;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class ManageRoles extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.pages.manage-roles';

    protected static ?string $navigationGroup = 'System Management';

    protected static ?int $navigationSort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(Role::with('permissions'))
            ->columns([
                TextColumn::make('name')
                    ->label('Role Name')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('edit_permissions')
                    ->label('Edit Permissions')
                    ->icon('heroicon-o-key')
                    ->modalHeading(fn (Role $record) => "Edit Permissions for {$record->name}")
                    ->form([
                        CheckboxList::make('permissions')
                            ->label('Permissions')
                            ->options(function () {
                                return Permission::all()
                                    ->groupBy(function ($permission) {
                                        return explode('.', $permission->name)[0];
                                    })
                                    ->mapWithKeys(function ($permissions, $group) {
                                        $groupName = ucfirst(str_replace('_', ' ', $group));
                                        return [
                                            $groupName => $permissions->mapWithKeys(function ($permission) {
                                                return [$permission->name => $permission->name];
                                            })->toArray(),
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3),
                    ])
                    ->action(function (array $data, Role $record) {
                        $record->syncPermissions($data['permissions']);
                    })
                    ->mountUsing(function (Role $record) {
                        return [
                            'permissions' => $record->permissions->pluck('name')->toArray(),
                        ];
                    }),
            ]);
    }

    protected function getPermissionGroups(): array
    {
        return Permission::all()
            ->groupBy(function ($permission) {
                return explode('.', $permission->name)[0];
            })
            ->mapWithKeys(function ($permissions, $group) {
                return [
                    $group => $permissions->pluck('name')->toArray(),
                ];
            })
            ->toArray();
    }

    public static function canAccess(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('admins.update');
    }
}