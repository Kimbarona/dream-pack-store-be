<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TagsColumn;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Accounts';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Permission Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Permission Name')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('guard_name')
                            ->required()
                            ->label('Guard Name')
                            ->default('admin')
                            ->disabled()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('group')
                            ->label('Group')
                            ->maxLength(255)
                            ->helperText('Group permissions for better organization'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Permission Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('group')
                    ->label('Group')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => match ($record->group) {
                        'Administrators' => 'danger',
                        'Product Management' => 'warning',
                        'Order Management' => 'info',
                        'Category Management' => 'success',
                        'Banner Management' => 'primary',
                        'Settings' => 'gray',
                        default => 'secondary',
                    }),

                TagsColumn::make('roles.name')
                    ->label('Assigned to Roles')
                    ->limit(3)
                    ->separator(', '),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options(function () {
                        return Permission::where('guard_name', 'admin')
                            ->distinct('group')
                            ->pluck('group', 'group')
                            ->toArray();
                    })
                    ->label('Filter by Group'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        // Prevent deletion if permission is assigned to roles
                        if ($record->roles()->count() > 0) {
                            return false;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Prevent deletion of permissions assigned to roles
                            return $records->reject(function ($record) {
                                return $record->roles()->count() > 0;
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'view' => Pages\ViewPermission::route('/{record}'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
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
        return $user && $user->hasPermissionTo('roles.manage');
    }

    public static function canDelete($record): bool
    {
        $user = Auth::guard('admin')->user();
        
        // Cannot delete permission if it has roles
        if ($record->roles()->count() > 0) {
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