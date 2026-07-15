<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Filament\Resources\PropertyResource\RelationManagers;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Propiedad')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la propiedad')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('zone')
                            ->label('Zona')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('total_area')
                            ->label('Superficie total (m²)')
                            ->numeric(),
                        Forms\Components\Select::make('property_type')
                            ->label('Tipo de propiedad')
                            ->options([
                                'casa' => 'Casa',
                                'departamento' => 'Departamento',
                                'oficina' => 'Oficina',
                                'campo' => 'Campo',
                                'quinta' => 'Quinta',
                                'country' => 'Country',
                                'otro' => 'Otro',
                            ])
                            ->default('casa'),
                    ])->columns(2),

                Forms\Components\Section::make('Jardines')
                    ->schema([
                        Forms\Components\Toggle::make('has_garden')
                            ->label('¿Tiene jardín?')
                            ->live(),
                        Forms\Components\Repeater::make('garden_areas')
                            ->label('Áreas de jardín')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required(),
                                Forms\Components\TextInput::make('size')
                                    ->label('Tamaño (m²)')
                                    ->numeric(),
                            ])
                            ->collapsible()
                            ->visible(fn ($get) => $get('has_garden')),
                    ]),

                Forms\Components\Section::make('Piscinas')
                    ->schema([
                        Forms\Components\Toggle::make('has_pool')
                            ->label('¿Tiene piscina?')
                            ->live(),
                        Forms\Components\Repeater::make('pools')
                            ->label('Piscinas')
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'climatizada' => 'Climatizada',
                                        'tradicional' => 'Tradicional',
                                        'infantil' => 'Infantil',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('liters')
                                    ->label('Litros')
                                    ->numeric(),
                                Forms\Components\TextInput::make('size_m2')
                                    ->label('Tamaño (m²)')
                                    ->numeric(),
                            ])
                            ->collapsible()
                            ->visible(fn ($get) => $get('has_pool')),
                    ]),

                Forms\Components\Section::make('Árboles')
                    ->schema([
                        Forms\Components\Toggle::make('has_trees')
                            ->label('¿Tiene árboles?')
                            ->live(),
                        Forms\Components\Repeater::make('trees')
                            ->label('Árboles')
                            ->schema([
                                Forms\Components\TextInput::make('species')
                                    ->label('Especie')
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1),
                                Forms\Components\TextInput::make('height')
                                    ->label('Altura (m)')
                                    ->numeric(),
                            ])
                            ->collapsible()
                            ->visible(fn ($get) => $get('has_trees')),
                    ]),

                Forms\Components\Section::make('Plantas')
                    ->schema([
                        Forms\Components\Toggle::make('has_plants')
                            ->label('¿Tiene plantas?')
                            ->live(),
                        Forms\Components\Repeater::make('plants')
                            ->label('Plantas')
                            ->schema([
                                Forms\Components\TextInput::make('species')
                                    ->label('Especie')
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1),
                            ])
                            ->collapsible()
                            ->visible(fn ($get) => $get('has_plants')),
                    ]),

                Forms\Components\Section::make('Áreas deportivas')
                    ->schema([
                        Forms\Components\Toggle::make('has_sport_areas')
                            ->label('¿Tiene áreas deportivas?')
                            ->live(),
                        Forms\Components\Repeater::make('sport_areas')
                            ->label('Áreas deportivas')
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'tenis' => 'Cancha de tenis',
                                        'paddle' => 'Cancha de paddle',
                                        'futbol' => 'Cancha de fútbol',
                                        'basket' => 'Cancha de básquet',
                                        'otros' => 'Otros',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1),
                            ])
                            ->collapsible()
                            ->visible(fn ($get) => $get('has_sport_areas')),
                    ]),

                Forms\Components\Section::make('Otras características')
                    ->schema([
                        Forms\Components\KeyValue::make('other_features')
                            ->label('Características adicionales'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Propiedad')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('zone')
                    ->label('Zona'),
                Tables\Columns\IconColumn::make('has_garden')
                    ->label('Jardín')
                    ->boolean(),
                Tables\Columns\IconColumn::make('has_pool')
                    ->label('Pileta')
                    ->boolean(),
                Tables\Columns\IconColumn::make('has_trees')
                    ->label('Árboles')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('zone')
                    ->label('Zona'),
                Tables\Filters\SelectFilter::make('property_type')
                    ->label('Tipo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PostsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}