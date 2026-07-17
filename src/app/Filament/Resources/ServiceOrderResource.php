<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceOrderResource\Pages;
use App\Models\ServiceOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationLabel = 'Órdenes de servicio';

    protected static ?string $modelLabel = 'orden de servicio';

    protected static ?string $pluralModelLabel = 'órdenes de servicio';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Radio::make('flow_type')
                    ->label('Tipo de flujo')
                    ->options(ServiceOrder::FLOW_TYPES)
                    ->default('con_relevamiento')
                    ->live()
                    ->afterStateUpdated(function (string $state, callable $set): void {
                        $set('status', $state === 'presupuesto_directo' ? 'presupuestado_enviado' : 'visita_programada');
                    })
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('property_id')
                    ->label('Propiedad')
                    ->relationship('property', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->customer?->name.' — '.$record->name)
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
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
                    ])
                    ->required(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento'),

                Forms\Components\Select::make('relevamiento_id')
                    ->label('Relevamiento vinculado')
                    ->relationship('relevamiento', 'id', fn ($query, callable $get) => $get('property_id')
                        ? $query->where('property_id', $get('property_id'))
                        : $query)
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->relevador?->name.' — '.($record->scheduled_date?->format('d/m/Y') ?? 'sin fecha'))
                    ->searchable()
                    ->preload()
                    ->visible(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento'),

                Forms\Components\DatePicker::make('work_date')
                    ->label('Fecha de trabajo')
                    ->visible(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento')
                    ->required(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento'),

                Forms\Components\Select::make('time_slot')
                    ->label('Franja horaria')
                    ->options(ServiceOrder::TIME_SLOTS)
                    ->visible(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento'),

                Forms\Components\SpatieMediaLibraryFileUpload::make('budget_photos')
                    ->collection('budget_photos')
                    ->label('Fotos para presupuesto')
                    ->multiple()
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/jfif', 'image/bmp', 'image/gif'])
                    ->imageEditor()
                    ->reorderable()
                    ->openable()
                    ->columnSpanFull()
                    ->visible(fn (callable $get): bool => $get('flow_type') === 'presupuesto_directo'),

                Forms\Components\Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('post_id')
                    ->label('Trabajo relacionado')
                    ->relationship('post', 'title')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options(ServiceOrder::allStatusOptions())
                    ->default('visita_programada')
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->label('Precio')
                    ->numeric()
                    ->prefix('$'),

                Forms\Components\Textarea::make('observations')
                    ->label('Observaciones')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->default('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('property.name')
                    ->label('Propiedad')
                    ->default('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría'),
                Tables\Columns\TextColumn::make('work_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('time_slot')
                    ->label('Franja')
                    ->formatStateUsing(fn (?string $state): string => $state ? ServiceOrder::TIME_SLOTS[$state] ?? $state : '—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'visita_programada', 'visita_realizada' => 'info',
                        'presupuestado_enviado', 'presupuesto_aceptado' => 'warning',
                        'trabajo_programado', 'conformidad_cliente' => 'primary',
                        'servicio_pagado', 'factura_enviada' => 'success',
                        'cancelado' => 'danger',
                        'reprogramado' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => (ServiceOrder::PIPELINE_STATUSES + ServiceOrder::OTHER_STATUSES)[$state] ?? $state),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('ARS'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(ServiceOrder::allStatusOptions()),
                Tables\Filters\SelectFilter::make('flow_type')
                    ->label('Tipo de flujo')
                    ->options(ServiceOrder::FLOW_TYPES),
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('work_date', 'desc');
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
            'index' => Pages\ListServiceOrders::route('/'),
            'create' => Pages\CreateServiceOrder::route('/create'),
            'edit' => Pages\EditServiceOrder::route('/{record}/edit'),
        ];
    }
}
