<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\FormatsThousandsInput;
use App\Filament\Concerns\HasPendingAttentionBadge;
use App\Filament\Resources\ServiceOrderResource\Pages;
use App\Filament\Resources\WorkOrderResource;
use App\Models\Relevamiento;
use App\Models\ServiceOrder;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceOrderResource extends Resource
{
    use FormatsThousandsInput;
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
                ...static::flowAndRelevamientoFields(),
                ...static::customerAndPropertyFields(),
                ...static::detailFields(),
            ]);
    }

    /**
     * Cliente/Propiedad — compartidos entre ServiceOrderResource::form()
     * y CreateServiceOrder (el form reducido de "Crear orden con foto",
     * que solo usa propertyField() — el cliente sale solo de la Propiedad
     * elegida, ver CreateServiceOrder::mutateFormDataBeforeCreate()).
     * Los closures de disabled()/helperText() dependen de flow_type: en
     * un form que no tiene ese campo $get('flow_type') da null, así que
     * quedan habilitados sin texto de ayuda — el comportamiento correcto.
     */
    public static function customerAndPropertyFields(): array
    {
        return [
            static::customerField(),
            static::propertyField(),
        ];
    }

    public static function customerField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('customer_id')
            ->label('Cliente')
            ->relationship('customer', 'name')
            ->searchable()
            ->preload()
            ->disabled(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento')
            ->dehydrated(true)
            ->helperText(fn (callable $get): ?string => $get('flow_type') === 'con_relevamiento'
                ? 'Se completa solo a partir del Relevamiento elegido arriba.'
                : null)
            ->required();
    }

    public static function propertyField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('property_id')
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
            ->required();
    }

    /**
     * Campos compartidos con ServiceOrdersRelationManager (la pestaña de
     * Órdenes de Servicio dentro de una Property) — se separan de
     * customer_id/property_id porque esos dos no tienen sentido ahí (la
     * Property y su cliente ya están dados por el contexto).
     */
    public static function flowAndRelevamientoFields(?Closure $relevamientoQueryModifier = null, ?Closure $relevamientoOptionLabel = null): array
    {
        return [
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
                ->relationship('relevamiento', 'id', modifyQueryUsing: $relevamientoQueryModifier ?? fn ($query) => $query->with('property.customer', 'relevador'))
                ->getOptionLabelFromRecordUsing($relevamientoOptionLabel ?? fn ($record) => collect([
                    $record->property?->customer?->name,
                    $record->property?->display_label,
                    $record->relevador?->name,
                    $record->scheduled_date?->format('d/m/Y') ?? 'sin fecha',
                ])->filter()->join(' — '))
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function (callable $set, ?string $state): void {
                    $relevamiento = $state ? Relevamiento::find($state) : null;
                    $set('customer_id', $relevamiento?->property?->customer_id);
                    $set('property_id', $relevamiento?->property_id);
                })
                ->required(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento')
                ->visible(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento'),
        ];
    }

    /**
     * Resto de los campos compartidos entre ServiceOrderResource y
     * ServiceOrdersRelationManager — ver flowAndRelevamientoFields().
     */
    public static function detailFields(): array
    {
        return [
            Forms\Components\DatePicker::make('work_date')
                ->label('Fecha estimada de visita')
                ->helperText('Opcional — orientativa para el relevador. No es la fecha de ejecución del trabajo: esa se agenda después, en la Orden de Trabajo.')
                ->visible(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento'),

            Forms\Components\Select::make('time_slot')
                ->label('Franja horaria de la visita')
                ->options(ServiceOrder::TIME_SLOTS)
                ->visible(fn (callable $get): bool => $get('flow_type') === 'con_relevamiento'),

            static::itemsRepeaterField()
                ->visible(fn (callable $get): bool => $get('flow_type') === 'presupuesto_directo'),

            Forms\Components\Select::make('category_id')
                ->label('Categoría')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options(function (string $operation, callable $get): array {
                    if ($operation !== 'create') {
                        return ServiceOrder::allStatusOptions();
                    }

                    $initialStatus = $get('flow_type') === 'presupuesto_directo' ? 'presupuestado_enviado' : 'visita_programada';

                    return [$initialStatus => ServiceOrder::PIPELINE_STATUSES[$initialStatus]];
                })
                ->default('visita_programada')
                ->required(),

            static::priceField()
                ->visible(fn (callable $get): bool => $get('flow_type') === 'presupuesto_directo'),

            Forms\Components\Textarea::make('observations')
                ->label('Observaciones')
                ->columnSpanFull(),

            Forms\Components\Select::make('post_id')
                ->label('Trabajo relacionado')
                ->helperText('Solo para vincular con contenido del sitio — no afecta el flujo operativo.')
                ->relationship('post', 'title')
                ->searchable()
                ->preload(),
        ];
    }

    /**
     * Ítems del flujo "presupuesto directo por foto" — mismo esquema que
     * los ítems del Relevamiento (descripción, observaciones, incluye
     * retiro, fotos), para que "Revisar y presupuestar" y la proforma
     * pública los muestren con el mismo componente sin importar el
     * origen. Se usa tanto acá (form completo, condicionado a
     * flow_type) como en el form reducido de "Crear orden con foto".
     */
    public static function itemsRepeaterField(): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('items')
            ->relationship()
            ->label('Ítems')
            ->schema([
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->rows(2),
                Forms\Components\Textarea::make('observations')
                    ->label('Observaciones')
                    ->rows(2),
                Forms\Components\Toggle::make('includes_pickup')
                    ->label('Incluye retiro'),
                Forms\Components\SpatieMediaLibraryFileUpload::make('photos')
                    ->collection('photos')
                    ->label('Fotos')
                    ->helperText('La(s) foto(s) que mandó el cliente para este ítem.')
                    ->multiple()
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/jfif', 'image/bmp', 'image/gif'])
                    ->imageEditor()
                    ->reorderable()
                    ->openable()
                    ->required()
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->addActionLabel('+ Agregar ítem')
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => $state['description'] ?? null)
            ->columnSpanFull()
            ->minItems(1);
    }

    /**
     * Campos del form reducido de "Crear orden con foto" (sin
     * customer_id/property_id, que cada contexto de creación resuelve
     * distinto — ver CreateServiceOrder y ServiceOrdersRelationManager).
     */
    public static function photoFlowFields(): array
    {
        return [
            Forms\Components\Select::make('category_id')
                ->label('Categoría')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->required(),

            static::itemsRepeaterField(),

            static::priceField(),
        ];
    }

    /**
     * Campo "Precio" — compartido entre detailFields() (form completo) y
     * photoFlowFields() (form reducido de "Crear orden con foto"), con
     * separador de miles mientras se escribe (ver
     * App\Filament\Concerns\FormatsThousandsInput).
     */
    public static function priceField(): Forms\Components\TextInput
    {
        return static::thousandsFormattedTextInput('price')
            ->label('Precio')
            ->prefix('$')
            ->helperText('Precio de referencia inicial. El precio que se le manda al cliente se carga después, en "Revisar y presupuestar".');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('budget_number')
                    ->label('N° Presupuesto')
                    ->searchable(query: fn ($query, string $search) => $query->whereDocumentNumberLike($search))
                    ->toggleable(),
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
                    ->color(fn (string $state): string => static::statusColor($state))
                    ->formatStateUsing(fn (string $state): string => static::statusLabel($state)),
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

    /**
     * Compartido con ServiceOrdersRelationManager, para que el color de
     * cada estado no se desincronice entre las dos tablas.
     */
    public static function statusColor(string $state): string
    {
        return match ($state) {
            'visita_programada', 'visita_realizada' => 'info',
            'presupuestado_enviado' => 'warning',
            'presupuesto_aceptado' => 'success',
            'trabajo_programado', 'conformidad_cliente' => 'primary',
            'servicio_pagado', 'factura_enviada' => 'success',
            'cancelado' => 'danger',
            'reprogramado' => 'gray',
            default => 'gray',
        };
    }

    public static function statusLabel(string $state): string
    {
        return (ServiceOrder::PIPELINE_STATUSES + ServiceOrder::OTHER_STATUSES)[$state] ?? $state;
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
