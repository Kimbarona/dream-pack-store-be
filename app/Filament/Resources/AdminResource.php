<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Models\Admin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TagsColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Accounts';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Admin Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->label('Name')
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->label('Email')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->password()
                            ->label('Password')
                            ->required(fn ($context) => $context === 'create')
                            ->dehydrateStateUsing(fn ($state) => $state ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->helperText('Leave empty to keep current password'),

                        Select::make('roles')
                            ->relationship('roles', 'name', function ($query) {
                                $user = Auth::guard('admin')->user();
                                
                                // Only super admins can assign super-admin role
                                if (!$user || !$user->hasRole('Super Admin')) {
                                    $query->where('name', '!=', 'Super Admin');
                                }
                                
                                return $query->where('guard_name', 'admin');
                            })
                            ->multiple()
                            ->preload()
                            ->label('Roles')
                            ->required()
                            ->searchable()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get, callable $livewire) {
                                $record = $livewire->getRecord();
                                $hasCoAdminRole = false;
                                
                                // Check if roles include co-admin
                                if (is_array($state)) {
                                    $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $state)->pluck('name')->toArray();
                                    $hasCoAdminRole = in_array('co-admin', $roleNames);
                                }
                                
                                // Auto-populate defaults for co-admin
                                if ($hasCoAdminRole && (!$record || empty($get('enabled_modules')))) {
                                    $set('enabled_modules', \App\Models\Admin::getDefaultEnabledModules());
                                }
                            })
                            ->dehydrateStateUsing(function ($state) {
                                $user = Auth::guard('admin')->user();
                                
                                // Prevent non-super admins from assigning super-admin role
                                if (!$user || !$user->hasRole('Super Admin')) {
                                    if (is_array($state)) {
                                        $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $state)->pluck('name')->toArray();
                                        $state = array_filter($state, function ($roleId) use ($roleNames) {
                                            $roleName = \Spatie\Permission\Models\Role::find($roleId)?->name;
                                            return $roleName !== 'Super Admin';
                                        });
                                    }
                                }
                                
                                return $state;
                            }),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                Section::make('Enabled Modules')
                    ->description('Configure module access for co-admin users. Super admins and admins have access to all modules.')
                    ->schema([
                        Placeholder::make('module_info')
                            ->content('Module access is automatically synced with permissions.')
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                Select::make('enabled_modules.orders')
                                    ->label('Orders')
                                    ->options([
                                        'none' => 'No Access',
                                        'view' => 'View Only',
                                        'manage' => 'Manage',
                                    ])
                                    ->default('none')
                                    ->required(),

                                Select::make('enabled_modules.products')
                                    ->label('Products')
                                    ->options([
                                        'none' => 'No Access',
                                        'view' => 'View Only',
                                        'manage' => 'Manage',
                                    ])
                                    ->default('none')
                                    ->required(),

                                Select::make('enabled_modules.categories')
                                    ->label('Categories')
                                    ->options([
                                        'none' => 'No Access',
                                        'view' => 'View Only',
                                        'manage' => 'Manage',
                                    ])
                                    ->default('none')
                                    ->required(),

                                Select::make('enabled_modules.banners')
                                    ->label('Banners')
                                    ->options([
                                        'none' => 'No Access',
                                        'view' => 'View Only',
                                        'manage' => 'Manage',
                                    ])
                                    ->default('none')
                                    ->required(),

                                Select::make('enabled_modules.settings')
                                    ->label('Settings')
                                    ->options([
                                        'none' => 'No Access',
                                        'manage' => 'Manage',
                                    ])
                                    ->default('none')
                                    ->required(),

                                Select::make('enabled_modules.admin_users')
                                    ->label('Admin Users')
                                    ->options([
                                        'none' => 'No Access',
                                        'manage' => 'Manage',
                                    ])
                                    ->default('none')
                                    ->required(),

                                Select::make('enabled_modules.role_management')
                                    ->label('Role Management')
                                    ->options([
                                        'none' => 'No Access',
                                        'manage' => 'Manage',
                                    ])
                                    ->default('none')
                                    ->required(),
                            ])
                            ->visible(function (callable $get) {
                                $roles = $get('roles');
                                if (!is_array($roles)) {
                                    return false;
                                }
                                
                                $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $roles)->pluck('name')->toArray();
                                
                                // Only show for co-admin roles
                                return in_array('co-admin', $roleNames) && !in_array('Super Admin', $roleNames) && !in_array('Admin', $roleNames);
                            }),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TagsColumn::make('roles.name')
                    ->label('Roles')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->label('Filter by Roles'),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        // Prevent deletion of super admin
                        if ($record->hasRole('Super Admin')) {
                            return false;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Prevent deletion of super admins
                            return $records->reject(function ($record) {
                                return $record->hasRole('Super Admin');
                            });
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'view' => Pages\ViewAdmin::route('/{record}'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
            'roles' => Pages\ManageRoles::route('/roles'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function canViewAny(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('admins.view');
    }

    public static function canCreate(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('admins.create');
    }

    public static function canEdit($record): bool
    {
        $user = Auth::guard('admin')->user();
        
        // Cannot edit if you don't have permission
        if (!$user || !$user->hasPermissionTo('admins.update')) {
            return false;
        }
        
        // Cannot edit yourself
        if ($record->id === $user->id) {
            return false;
        }
        
        // Super admin can edit anyone except themselves (already checked)
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        
        // Admin cannot edit super admin
        if ($record->hasRole('Super Admin')) {
            return false;
        }
        
        return true;
    }

    public static function canDelete($record): bool
    {
        $user = Auth::guard('admin')->user();
        
        // Cannot delete if you don't have permission
        if (!$user || !$user->hasPermissionTo('admins.delete')) {
            return false;
        }
        
        // Cannot delete super admin
        if ($record->hasRole('Super Admin')) {
            return false;
        }
        
        // Cannot delete yourself
        if ($record->id === $user->id) {
            return false;
        }
        
        return true;
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('admins.delete');
    }
}