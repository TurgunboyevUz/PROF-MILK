<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;

use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';

    protected static ?string $navigationLabel = 'Продукты';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основное')
                    ->description('Основные данные')
                    ->collapsible()
                    ->columns(2)->schema([
                    Select::make('category_id')->label('Категория')->required()->options(Category::all()->pluck('name_ru', 'id')),
                    TextInput::make('price')->label('Цена')->required()->numeric()->minValue(0),
                    TextInput::make('name_uz')->label('Название (UZ)')->required()->maxLength(255),
                    TextInput::make('name_ru')->label('Название (RU)')->required()->maxLength(255),
                    Textarea::make('description_uz')->label('Описание (UZ)')->required()->maxLength(2000),
                    Textarea::make('description_ru')->label('Описание (RU)')->required()->maxLength(2000),
                    FileUpload::make('image')->label('Изображение')->image()->required()->columnSpanFull(),
                ]),

                Section::make('Налог')
                    ->description('Налоговые данные')
                    ->columns(2)->schema([
                    TextInput::make('code')->label('Код')->required()->maxLength(255),
                    TextInput::make('vat_percent')->label('НДС (%)')->required()->numeric()->minValue(0),
                    TextInput::make('package_code')->label('Код упаковки')->required()->maxLength(255),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('category.name_ru')->label('Категория')->sortable()->searchable()->toggleable(),
                ImageColumn::make('image')->label('Изображение')->toggleable(),

                TextColumn::make('name_uz')->label('Название (UZ)')
                ->description(fn (Product $record): string => Str::limit($record->description_uz, 20))
                ->sortable()->searchable()->toggleable(),

                TextColumn::make('name_ru')
                ->description(fn (Product $record): string => Str::limit($record->description_ru, 20))
                ->label('Название (RU)')->sortable()->searchable()->toggleable(),
                
                //TextColumn::make('description_uz')->label('Описание (UZ)')->sortable()->searchable()->toggleable(),
                //TextColumn::make('description_ru')->label('Описание (RU)')->sortable()->searchable()->toggleable(),
                TextColumn::make('price')->label('Цена')->sortable()->searchable()->toggleable(),
                TextColumn::make('code')->label('Код')->sortable()->searchable()->toggleable(),
                TextColumn::make('vat_percent')->label('НДС (%)')->sortable()->searchable()->toggleable(),
                TextColumn::make('package_code')->label('Код упаковки')->sortable()->searchable()->toggleable(),
                TextColumn::make('created_at')->label('Создан')->sortable()->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
