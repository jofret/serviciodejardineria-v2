<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PropertiesRelationManager extends RelationManager
{
    protected static string $relationship = 'properties';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Forms\Components\Toggle::make('has_garden')
                    ->label('¿Tiene jardín?'),
                Forms\Components\Toggle::make('has_pool')
                    ->label('¿Tiene piscina?'),
                Forms\Components\Toggle::make('has_trees')
                    ->label('¿Tiene árboles?'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('display_label')
            ->columns([
                Tables\Columns\TextColumn::make('display_label')
                    ->label('Propiedad')
                    ->searchable(query: fn ($query, string $search) => $query
                        ->where('address', 'like', "%{$search}%")
                        ->orWhere('zone', 'like', "%{$search}%")),
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
                    ->dateTime('d/m/Y'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
