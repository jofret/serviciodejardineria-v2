<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresupuestoPorFotoResource\Pages;
use App\Models\Category;
use App\Models\Property;
use App\Models\Relevamiento;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Presupuestos armados directamente por el admin a partir de fotos que
 * mandó el cliente, sin pasar por el flujo de asignación a un relevador.
 * Usa el mismo modelo Relevamiento (y los mismos componentes: ítems de
 * trabajo con retiro y fotos, herramientas, precio estimativo) que ya
 * usa el panel del relevador — la única diferencia real es que acá la
 * Propiedad se elige directo y el registro "nace enviado" (ver
 * CreatePresupuestoPorFoto::afterCreate()).
 *
 * Comparte la tabla `relevamientos` con RelevamientoResource; se
 * distingue por no tener relevador asignado (assigned_to null), algo
 * que nunca pasa en el flujo normal de asignación (ahí es obligatorio).
 */
class PresupuestoPorFotoResource extends Resource
{
    protected static ?string $model = Relevamiento::class;

    protected static ?string $slug = 'presupuestos-por-foto';

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    protected static ?string $navigationGroup = 'Relevamientos';

    protected static ?string $navigationLabel = 'Presupuestos por foto';

    protected static ?string $modelLabel = 'presupuesto por foto';

    protected static ?string $pluralModelLabel = 'presupuestos por foto';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNull('assigned_to');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos generales')
                    ->schema([
                        Forms\Components\Select::make('property_id')
                            ->label('Propiedad')
                            ->relationship('property', 'address')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->customer?->name.' — '.$record->display_label)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('property_type')
                            ->label('Tipo de Propiedad')
                            ->options(Property::PROPERTY_TYPES)
                            ->live()
                            ->afterStateHydrated(function (callable $set, $state) {
                                if ($state && ! array_key_exists($state, Property::PROPERTY_TYPES)) {
                                    $set('property_type_other', $state);
                                    $set('property_type', 'otro');
                                }
                            })
                            ->dehydrateStateUsing(fn ($state, callable $get) => $state === 'otro' && filled($get('property_type_other'))
                                ? $get('property_type_other')
                                : $state)
                            ->required(),
                        Forms\Components\TextInput::make('property_type_other')
                            ->label('Especificar tipo de propiedad')
                            ->maxLength(255)
                            ->visible(fn (callable $get): bool => $get('property_type') === 'otro')
                            ->required(fn (callable $get): bool => $get('property_type') === 'otro')
                            ->dehydrated(false),

                        Forms\Components\Select::make('category_id')
                            ->label('Tipo de trabajo')
                            ->options(fn () => Category::query()->orderBy('order')->pluck('name', 'id')->put('otro', 'Otro'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('category_other')
                            ->label('Especificar tipo de trabajo')
                            ->maxLength(255)
                            ->visible(fn (callable $get): bool => $get('category_id') === 'otro')
                            ->required(fn (callable $get): bool => $get('category_id') === 'otro'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Trabajo a realizar')
                    ->schema([
                        static::workItemsField(),
                    ]),

                Forms\Components\Section::make('Otros datos')
                    ->schema([
                        Forms\Components\Toggle::make('requires_non_compete_clause')
                            ->label('Requiere Cláusula de No-Repetición')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('workTools')
                            ->relationship('workTools', 'name')
                            ->label('Herramientas para Realizar el Trabajo')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required(),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('workers_count')
                            ->label('Trabajadores para la Obra')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        Forms\Components\TextInput::make('estimated_duration_days')
                            ->label('Duración Aproximada de la Obra (días)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        static::estimatedPriceField(),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Mismo esquema que los ítems del formulario del relevador
     * (RelevamientoWorkItem: descripción, observaciones, incluye retiro,
     * fotos) — acá vía Filament en vez del formulario propio del
     * relevador, pero es el mismo modelo y por lo tanto el mismo
     * comportamiento aguas abajo (Revisar y presupuestar, proforma
     * pública, checklist de la Orden de Trabajo).
     */
    public static function workItemsField(): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('workItems')
            ->relationship()
            ->label('Ítems')
            ->schema([
                Forms\Components\Textarea::make('description')
                    ->label('Descripción del Trabajo')
                    ->rows(2),
                Forms\Components\Textarea::make('observations')
                    ->label('Observaciones')
                    ->rows(2),
                Forms\Components\Toggle::make('includes_pickup')
                    ->label('Incluye retiro'),
                Forms\Components\SpatieMediaLibraryFileUpload::make('photos')
                    ->collection('photos')
                    ->label('Fotos')
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
            ->addActionLabel('+ Agregar ítem de trabajo')
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => $state['description'] ?? null)
            ->columnSpanFull()
            ->minItems(1);
    }

    /**
     * "Precio Estimativo" con punto de miles y coma decimal mientras se
     * escribe (ej. "1.234.567,50"). El formateo en sí ocurre en el
     * navegador (window.formatThousandsInput, registrado en
     * AdminPanelProvider solo para las páginas de este recurso) porque
     * el bundle de Filament instalado acá no trae el plugin de Alpine
     * ($money) que el ->mask() nativo necesitaría.
     */
    public static function estimatedPriceField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('estimated_price')
            ->label('Precio Estimativo')
            ->type('text')
            ->inputMode('decimal')
            ->prefix('$')
            ->required()
            ->extraInputAttributes(['x-on:input' => 'window.formatThousandsInput($event)'])
            ->afterStateHydrated(fn ($component, $state) => $component->state(static::formatPriceDisplay($state)))
            ->dehydrateStateUsing(fn ($state) => static::parsePriceInput($state))
            ->rule(static fn (): Closure => function (string $attribute, $value, Closure $fail) {
                if (filled($value) && ! is_numeric(static::parsePriceInput($value))) {
                    $fail('El precio estimativo debe ser un valor numérico.');
                }
            });
    }

    private static function formatPriceDisplay($state): ?string
    {
        if (blank($state)) {
            return null;
        }

        $normalized = static::parsePriceInput($state);

        if (! is_numeric($normalized)) {
            return (string) $state;
        }

        [$intPart, $decPart] = array_pad(explode('.', $normalized, 2), 2, null);

        $formattedInt = number_format((float) $intPart, 0, '', '.');

        return $decPart !== null ? $formattedInt.','.$decPart : $formattedInt;
    }

    private static function parsePriceInput($state): ?string
    {
        if (blank($state)) {
            return null;
        }

        return str_replace(['.', ','], ['', '.'], (string) $state);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property.customer.name')
                    ->label('Cliente')
                    ->default('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('property.display_label')
                    ->label('Propiedad')
                    ->default('—'),
                Tables\Columns\TextColumn::make('service_type_label')
                    ->label('Tipo de trabajo')
                    ->default('—'),
                Tables\Columns\TextColumn::make('estimated_price')
                    ->label('Precio Estimativo')
                    ->money('ARS'),
                Tables\Columns\IconColumn::make('tiene_orden')
                    ->label('Orden generada')
                    ->boolean()
                    ->getStateUsing(fn (Relevamiento $record): bool => $record->serviceOrder !== null),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('property.customer', 'category', 'serviceOrder'))
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Tipo de trabajo')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('review_and_quote')
                    ->label('Revisar y presupuestar')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->visible(fn (Relevamiento $record): bool => $record->serviceOrder !== null)
                    ->url(fn (Relevamiento $record): string => ServiceOrderResource::getUrl('review-and-quote', ['record' => $record->serviceOrder])),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPresupuestosPorFoto::route('/'),
            'create' => Pages\CreatePresupuestoPorFoto::route('/create'),
            'edit' => Pages\EditPresupuestoPorFoto::route('/{record}/edit'),
        ];
    }
}
