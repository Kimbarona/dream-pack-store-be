<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TagsColumn;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Role Name')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('guard_name')
                            ->required()
                            ->label('Guard Name')
                            ->default('admin')
                            ->disabled()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->label('Permissions')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3)
                            ->required()
                            ->options(function () {
                                return Permission::where('guard_name', 'admin')
                                    ->orderBy('group')
                                    ->orderBy('name')
                                    ->get()
                                    ->groupBy('group')
                                    ->map(function ($group) {
                                        return $group->pluck('name', 'name');
                                    })
                                    ->toArray();
                            })
                            ->descriptions(function () {
                                return Permission::where('guard_name', 'admin')
                                    ->orderBy('group')
                                    ->orderBy('name')
                                    ->get()
                                    ->groupBy('group')
                                    ->map(function ($group) {
                                        return $group->pluck('name', 'name')
                                            ->mapWithKeys(function ($permission) {
                                                return [$permission => ''];
                                            });
                                    })
                                    ->flatten()
                                    ->toArray();
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Role Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->searchable()
                    ->sortable(),

                TagsColumn::make('permissions.name')
                    ->label('Permissions')
                    ->limit(3)
                    ->separator(', '),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        // Prevent deletion of super-admin role
                        if ($record->name === 'Super Admin') {
                            return false;
                        }
                        
                        // Prevent deletion if role is assigned to users
                        if ($record->users()->count() > 0) {
                            return false;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Prevent deletion of super-admin role and roles with users
                            return $records->reject(function ($record) {
                                return $record->name === 'Super Admin' || $record->users()->count() > 0;
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('guard_name', 'admin');
    }

    public static function canViewAny(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('roles.manage');
    }

    public static function canCreate(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('roles.manage');
    }

    public static function canEdit($record): bool
    {
        $user = Auth::guard('admin')->user();
        
        // Cannot edit super-admin role unless you are super-admin
        if ($record->name === 'Super Admin' && !$user->hasRole('Super Admin')) {
            return false;
        }
        
        return $user && $user->hasPermissionTo('roles.manage');
    }

    public static function canDelete($record): bool
    {
        $user = Auth::guard('admin')->user();
        
        // Cannot delete super-admin role
        if ($record->name === 'Super Admin') {
            return false;
        }
        
        // Cannot delete role if it has users
        if ($record->users()->count() > 0) {
            return false;
        }
        
        return $user && $user->hasPermissionTo('roles.manage');
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('roles.manage');
    }
}