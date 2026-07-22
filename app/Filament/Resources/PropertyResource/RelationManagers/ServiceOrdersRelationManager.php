<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use App\Models\ServiceOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceOrders';

    protected static ?string $title = 'Órdenes de servicio';

    protected static ?string $modelLabel = 'orden de servicio';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Sin selector visible: hoy el único flujo funcional es
                // "con_relevamiento" — ver el mismo comentario en
                // ServiceOrderResource::form(). Se mantiene como campo
                // oculto, no como valor hardcodeado en el modelo, para no
                // romper las condiciones visible()/required() de abajo.
                Forms\Components\Hidden::make('flow_type')
                    ->default('con_relevamiento'),

                Forms\Components\Select::make('relevamiento_id')
                    ->label('Relevamiento vinculado')
                    ->relationship('relevamiento', 'id', fn ($query) => $query->where('property_id', $this->getOwnerRecord()->id))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->relevador?->name.' — '.($record->scheduled_date?->format('d/m/Y') ?? 'sin fecha'))
                    ->searchable()
                    ->preload()
                    ->required(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento')
                    ->visible(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento'),

                Forms\Components\Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

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

    /**
     * Vista de solo lectura para una orden ya creada — mismo criterio que
     * ServiceOrderResource::infolist(). La única excepción editable es
     * "Observaciones", vía la acción de fila "edit_observations".
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('relevamiento_id')
                    ->label('Relevamiento vinculado')
                    ->getStateUsing(fn (ServiceOrder $record): string => $record->relevamiento
                        ? $record->relevamiento->relevador?->name.' — '.($record->relevamiento->scheduled_date?->format('d/m/Y') ?? 'sin fecha')
                        : '—'),

                Infolists\Components\TextEntry::make('category.name')
                    ->label('Categoría'),

                Infolists\Components\TextEntry::make('status')
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

                Infolists\Components\TextEntry::make('price')
                    ->label('Precio')
                    ->getStateUsing(fn (ServiceOrder $record) => $record->relevamiento?->estimated_price ?? $record->price)
                    ->money('ARS')
                    ->placeholder('—'),

                Infolists\Components\TextEntry::make('final_price')
                    ->label('Precio final')
                    ->money('ARS')
                    ->placeholder('—'),

                Infolists\Components\TextEntry::make('observations')
                    ->label('Observaciones')
                    ->placeholder('Sin observaciones')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear orden de servicio')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['customer_id'] = $this->getOwnerRecord()->customer_id;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('edit_observations')
                    ->label('Editar observaciones')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->form([
                        Forms\Components\Textarea::make('observations')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                    ])
                    ->fillForm(fn (ServiceOrder $record): array => [
                        'observations' => $record->observations,
                    ])
                    ->action(function (ServiceOrder $record, array $data): void {
                        $record->update($data);

                        Notification::make()
                            ->title('Observaciones actualizadas')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
