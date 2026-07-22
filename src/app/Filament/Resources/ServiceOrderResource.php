<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPendingAttentionBadge;
use App\Filament\Resources\ServiceOrderResource\Pages;
use App\Filament\Resources\WorkOrderResource;
use App\Models\ServiceOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceOrderResource extends Resource
{
    use HasPendingAttentionBadge;

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

                Forms\Components\Select::make('relevamiento_id')
                    ->label('Relevamiento vinculado')
                    ->relationship('relevamiento', 'id', modifyQueryUsing: fn ($query) => $query->with('property.customer', 'relevador'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => collect([
                        $record->property?->customer?->name,
                        $record->property?->display_label,
                        $record->relevador?->name,
                        $record->scheduled_date?->format('d/m/Y') ?? 'sin fecha',
                    ])->filter()->join(' — '))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (callable $set, ?string $state): void {
                        $relevamiento = $state ? \App\Models\Relevamiento::find($state) : null;
                        $set('customer_id', $relevamiento?->property?->customer_id);
                        $set('property_id', $relevamiento?->property_id);
                    })
                    ->required(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento')
                    ->visible(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento'),

                Forms\Components\Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->disabled(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento')
                    ->dehydrated(true)
                    ->helperText(fn (callable $get): ?string => $get('flow_type') === 'con_relevamiento'
                        ? 'Se completa solo a partir del Relevamiento elegido arriba.'
                        : null)
                    ->required(),

                Forms\Components\Select::make('property_id')
                    ->label('Propiedad')
                    ->relationship('property', 'address')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->customer?->name.' — '.$record->display_label)
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('zone')
                            ->label('Zona')
                            ->maxLength(255),
                    ])
                    ->disabled(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento')
                    ->dehydrated(true)
                    ->helperText(fn (callable $get): ?string => $get('flow_type') === 'con_relevamiento'
                        ? 'Se completa sola a partir del Relevamiento elegido arriba.'
                        : null)
                    ->required(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento'),

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
                Tables\Columns\TextColumn::make('property.display_label')
                    ->label('Propiedad')
                    ->default('—'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'visita_programada', 'visita_realizada' => 'info',
                        'presupuestado_enviado' => 'warning',
                        'presupuesto_aceptado' => 'success',
                        'trabajo_programado', 'conformidad_cliente' => 'primary',
                        'servicio_pagado', 'factura_enviada' => 'success',
                        'cancelado' => 'danger',
                        'reprogramado' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => (ServiceOrder::PIPELINE_STATUSES + ServiceOrder::OTHER_STATUSES)[$state] ?? $state),
                Tables\Columns\TextColumn::make('view_budget')
                    ->label('')
                    ->getStateUsing(fn (ServiceOrder $record): ?string => $record->status === 'presupuesto_aceptado' && $record->budget_token !== null ? 'Ver presupuesto' : null)
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn (ServiceOrder $record): ?string => $record->budget_token ? route('budget.show', $record->budget_token) : null, shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->getStateUsing(fn (ServiceOrder $record) => $record->relevamiento?->estimated_price ?? $record->price)
                    ->money('ARS'),
                Tables\Columns\TextColumn::make('final_price')
                    ->label('Precio final')
                    ->money('ARS')
                    ->color('success')
                    ->placeholder('—'),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('relevamiento', 'workOrder'))
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
                Tables\Actions\Action::make('review_and_quote')
                    ->label('Revisar y presupuestar')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->visible(fn (ServiceOrder $record): bool => $record->canReviewAndQuote() && $record->status !== 'presupuesto_aceptado')
                    ->url(fn (ServiceOrder $record): string => static::getUrl('review-and-quote', ['record' => $record])),
                Tables\Actions\Action::make('view_work_order')
                    ->label('Ver orden de trabajo')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('gray')
                    ->visible(fn (ServiceOrder $record): bool => $record->status === 'presupuesto_aceptado' && $record->workOrder !== null)
                    ->url(fn (ServiceOrder $record): string => WorkOrderResource::getUrl('edit', ['record' => $record->workOrder])),
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
            'review-and-quote' => Pages\ReviewAndQuote::route('/{record}/revisar-presupuestar'),
        ];
    }

    protected static function pendingAttentionCount(): int
    {
        return static::getModel()::whereNotNull('relevamiento_id')
            ->whereHas('relevamiento', fn ($query) => $query->whereNotNull('submitted_at'))
            ->whereNull('budget_sent_at')
            ->count();
    }

    protected static function pendingAttentionTooltip(): ?string
    {
        return 'Órdenes con relevamiento enviado, listas para revisar y presupuestar';
    }

    /**
     * Aviso dinámico para el modal de "Eliminar" — el borrado de una Orden
     * de Servicio arrastra en cascada su Orden de Trabajo (con checklist y
     * fotos), si ya se generó.
     */
    public static function deleteWarning(ServiceOrder $record): ?string
    {
        $workOrder = $record->workOrder;

        if (! $workOrder) {
            return null;
        }

        $mensaje = 'Esta orden ya tiene una Orden de Trabajo generada. '
            .'Se va a borrar definitivamente junto con su checklist y sus fotos.';

        if ($workOrder->conformity_confirmed_at !== null) {
            $mensaje .= ' ⚠️ El cliente ya confirmó conformidad sobre ese trabajo.';
        }

        return $mensaje;
    }
}
