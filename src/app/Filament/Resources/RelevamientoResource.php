<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RelevamientoResource\Pages;
use App\Models\Relevamiento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RelevamientoResource extends Resource
{
    protected static ?string $model = Relevamiento::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Relevamientos';

    protected static ?string $navigationLabel = 'Relevamientos';

    protected static ?string $modelLabel = 'relevamiento';

    protected static ?string $pluralModelLabel = 'relevamientos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('property_id')
                    ->label('Propiedad')
                    ->relationship('property', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->customer?->name.' — '.$record->name)
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('assigned_to')
                    ->label('Relevador asignado')
                    ->relationship('relevador', 'name', fn ($query) => $query->where('role', 'relevador'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('scheduled_date')
                    ->label('Fecha programada'),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'enviado' => 'Enviado',
                    ])
                    ->default('pendiente')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('photos')
                    ->collection('photos')
                    ->label('Fotos cargadas por el relevador')
                    ->disabled()
                    ->image()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property.customer.name')
                    ->label('Cliente')
                    ->default('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('property.name')
                    ->label('Propiedad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('relevador.name')
                    ->label('Relevador')
                    ->default('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'enviado' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendiente' => 'Pendiente',
                        'enviado' => 'Enviado',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'enviado' => 'Enviado',
                    ]),
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Relevador')
                    ->relationship('relevador', 'name', fn ($query) => $query->where('role', 'relevador')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_date', 'desc');
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
            'index' => Pages\ListRelevamientos::route('/'),
            'create' => Pages\CreateRelevamiento::route('/create'),
            'edit' => Pages\EditRelevamiento::route('/{record}/edit'),
        ];
    }
}
