<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\BannerResource\RelationManagers\ImagesRelationManager;
use App\Models\Banner;
use App\Filament\Traits\HasModuleAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class BannerResource extends Resource
{
    use HasModuleAccess;

    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Store Management';

    protected static ?int $navigationSort = 4;

public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Banner Information')
                    ->description('Configure the banner content and display settings')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Banner Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Homepage Hero, Promo Banner')
                            ->helperText('Internal name for this banner'),

                        TextInput::make('link_url')
                            ->label('Link URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com')
                            ->helperText('URL where users will be redirected when clicking banner'),
                    ]),

                Section::make('Banner Image')
                    ->description('Upload the main banner image')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Banner Image')
                            ->image()
                            ->imageEditor()
                            ->directory('banners')
                            ->visibility('public')
                            ->disk('public')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                            ->maxSize(5120) // 5MB
                            ->columnSpanFull()
                            ->helperText('Upload the main banner image. Recommended size: 1920x1080px')
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                // If image is uploaded, clear any temporary data
                                if ($state && is_array($state)) {
                                    $set('temp_image_removed', false);
                                }
                            }),
                    ]),

                Section::make('Content')
                    ->description('Configure the banner text and messaging')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->maxLength(255)
                            ->placeholder('Enter banner title')
                            ->helperText('Main headline displayed on banner'),

                        Textarea::make('subtitle')
                            ->label('Subtitle')
                            ->rows(2)
                            ->maxLength(500)
                            ->placeholder('Enter subtitle or description')
                            ->helperText('Additional text displayed below title'),
                    ]),

                Section::make('Banner Settings')
                    ->description('Configure when and how this banner appears')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Show this banner on frontend'),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),

                        DateTimePicker::make('starts_at')
                            ->label('Start Date/Time')
                            ->helperText('When to start showing this banner (optional)'),
                        
                        DateTimePicker::make('ends_at')
                            ->label('End Date/Time')
                            ->helperText('When to stop showing this banner (optional)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->size(100)
                    ->circular(false)
                    ->defaultImageUrl(url('/images/default-banner.jpg'))
                    ->getStateUsing(fn ($record) => $record->image ? \Illuminate\Support\Facades\Storage::url($record->image) : null),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->placeholder('No title'),

                Tables\Columns\TextColumn::make('link_url')
                    ->label('Link')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder('No link'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('scheduled')
                    ->label('Scheduled')
                    ->getStateUsing(fn (Banner $record): bool => 
                        $record->starts_at || $record->ends_at
                    )
                    ->boolean()
                    ->trueIcon('heroicon-o-clock')
                    ->falseIcon('heroicon-o-calendar'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultPaginationPageOption(25)
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
                    ->url(fn (Banner $record): string => "/api/banners/{$record->id}")
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
            ImagesRelationManager::class,
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
        return static::canAccessResource('banners');
    }
}