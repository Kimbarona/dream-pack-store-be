<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Banner Content')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->maxLength(255)
                            ->placeholder('Enter banner title')
                            ->helperText('Main headline for the banner'),

                        TextInput::make('link_url')
                            ->label('Link URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com')
                            ->helperText('URL where users will be redirected when clicking the banner'),
                    ]),

                Section::make('Subtitle')
                    ->schema([
                        Textarea::make('subtitle')
                            ->label('Subtitle')
                            ->rows(2)
                            ->maxLength(500)
                            ->placeholder('Enter subtitle or description')
                            ->helperText('Additional text displayed below the title'),
                    ]),

                Section::make('Banner Images')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Desktop Image')
                            ->image()
                            ->imageEditor()
                            ->directory('banners')
                            ->visibility('public')
                            ->maxSize(2048) // 2MB
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                            ->helperText('Desktop banner image (recommended: 1920x600px, max 2MB)'),

                        FileUpload::make('image_mobile_path')
                            ->label('Mobile Image (Optional)')
                            ->image()
                            ->imageEditor()
                            ->directory('banners')
                            ->visibility('public')
                            ->maxSize(1024) // 1MB
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                            ->helperText('Mobile banner image (recommended: 768x400px, max 1MB). If not provided, desktop image will be used.'),
                    ]),

                Section::make('Banner Settings')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Show this banner on the frontend'),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),

                        Grid::make(1)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label('Start Date/Time')
                                    ->helperText('When to start showing this banner (optional)'),
                            ]),
                        
                        Grid::make(1)
                            ->schema([
                                DateTimePicker::make('ends_at')
                                    ->label('End Date/Time')
                                    ->helperText('When to stop showing this banner (optional)'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->size(100)
                    ->circular(false)
                    ->defaultImageUrl(url('placeholder-image')),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(50),

                TextColumn::make('link_url')
                    ->label('Link')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(30),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('scheduled')
                    ->label('Scheduled')
                    ->getStateUsing(fn (Banner $record): bool => 
                        $record->starts_at || $record->ends_at
                    )
                    ->boolean()
                    ->trueIcon('heroicon-o-clock')
                    ->falseIcon('heroicon-o-calendar'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),

                Tables\Filters\Filter::make('scheduled')
                    ->label('Scheduled')
                    ->query(fn ($query) => $query->whereNotNull('starts_at')->orWhereNotNull('ends_at')),

                Tables\Filters\Filter::make('currently_active')
                    ->label('Currently Active')
                    ->query(fn ($query) => $query->active()->scheduled())
                    ->default(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Banner $record): string => route('banner.preview', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->reorderable('sort_order');
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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'view' => Pages\ViewBanner::route('/{record}'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('banners.view');
    }

    public static function canCreate(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('banners.create');
    }

    public static function canEdit($record): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('banners.update');
    }

    public static function canDelete($record): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('banners.delete');
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('banners.delete');
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::guard('admin')->user();
        if (!$user || !$user->hasPermissionTo('banners.view')) {
            return null;
        }

        return static::getModel()::active()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}