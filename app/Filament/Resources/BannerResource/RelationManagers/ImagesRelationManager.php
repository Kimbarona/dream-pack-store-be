<?php

namespace App\Filament\Resources\BannerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use App\Models\BannerImage;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('path')
                    ->label('Image')
                    ->image()
                    ->directory(fn () => 'banners/' . $this->ownerRecord->id)
                    ->visibility('public')
                    ->disk('public')
                    ->required()
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('disk')
                    ->label('Storage Disk')
                    ->default('public')
                    ->disabled()
                    ->helperText('Images are stored on the public disk'),
                    
                Forms\Components\Toggle::make('is_mobile')
                    ->label('Mobile Image')
                    ->helperText('Check if this image is specifically for mobile view')
                    ->default(false),
                    
                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('path')
            ->columns([
                Tables\Columns\ImageColumn::make('url')
                    ->label('Image')
                    ->size(80)
                    ->circular(false),
                    
                Tables\Columns\TextColumn::make('path')
                    ->label('File Path')
                    ->limit(40)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\IconColumn::make('is_mobile')
                    ->label('Mobile')
                    ->boolean()
                    ->trueIcon('heroicon-o-device-phone-mobile')
                    ->falseIcon('heroicon-o-computer-desktop'),
                    
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_mobile')
                    ->label('Image Type')
                    ->placeholder('All')
                    ->trueLabel('Mobile Images')
                    ->falseLabel('Desktop Images'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['disk'] = 'public';
                        return $data;
                    }),
                Tables\Actions\Action::make('uploadGallery')
                    ->label('Upload Gallery')
                    ->icon('heroicon-o-photo')
                    ->form([
                        Forms\Components\FileUpload::make('gallery')
                            ->label('Gallery Images')
                            ->multiple()
                            ->image()
                            ->directory(fn () => 'banners/' . $this->ownerRecord->id . '/gallery')
                            ->visibility('public')
                            ->disk('public')
                            ->required()
                            ->columnSpanFull()
                            ->maxFiles(20)
                            ->helperText('Upload multiple images. Each image will be created as a banner image entry.'),
                    ])
                    ->action(function (array $data) {
                        try {
                            $sortOrder = BannerImage::max('sort_order') ?? 0;
                            
                            foreach ($data['gallery'] as $index => $image) {
                                BannerImage::create([
                                    'banner_id' => $this->ownerRecord->id,
                                    'path' => $image,
                                    'disk' => 'public',
                                    'sort_order' => $sortOrder + $index,
                                    'is_mobile' => false,
                                ]);
                            }
                            
                            $this->dispatch('refreshComponent');
                        } catch (\Exception $e) {
                            logger('Gallery upload failed: ' . $e->getMessage());
                            throw $e;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, $record) {
                        // Delete the actual file from storage
                        if ($record->path && Storage::disk($record->disk)->exists($record->path)) {
                            Storage::disk($record->disk)->delete($record->path);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                            foreach ($records as $record) {
                                if ($record->path && Storage::disk($record->disk)->exists($record->path)) {
                                    Storage::disk($record->disk)->delete($record->path);
                                }
                            }
                        }),
                ]),
            ])
            ->reorderable('sort_order');
    }
}
