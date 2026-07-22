<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPendingAttentionBadge;
use App\Filament\Resources\WorkOrderResource\Pages;
use App\Models\ServiceOrder;
use App\Models\WorkOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkOrderResource extends Resource
{
    use HasPendingAttentionBadge;

    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationLabel = 'Órdenes de trabajo';

    protected static ?string $modelLabel = 'orden de trabajo';

    protected static ?string $pluralModelLabel = 'órdenes de trabajo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Orden de servicio vinculada')
                    ->description('Datos de solo lectura, cargados desde la Orden de Servicio original.')
                    ->schema([
                        Forms\Components\Placeholder::make('customer')
                            ->label('Cliente')
                            ->content(fn (?WorkOrder $record): string => $record?->serviceOrder?->customer?->name ?? '—'),
                        Forms\Components\Placeholder::make('property')
                            ->label('Propiedad')
                            ->content(fn (?WorkOrder $record): string => $record?->serviceOrder?->property?->display_label ?? '—'),
                        Forms\Components\Placeholder::make('category')
                            ->label('Categoría')
                            ->content(fn (?WorkOrder $record): string => $record?->serviceOrder?->category?->name ?? '—'),
                        Forms\Components\Placeholder::make('relevamiento')
                            ->label('Relevamiento')
                            ->content(fn (?WorkOrder $record): string => $record?->serviceOrder?->relevamiento
                                ? 'Relevamiento #'.$record->serviceOrder->relevamiento->id
                                : '—'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Programación')
                    ->schema([
                        Forms\Components\DatePicker::make('work_date')
                            ->label('Fecha de trabajo'),
                        Forms\Components\Select::make('time_slot')
                            ->label('Franja horaria')
                            ->options(ServiceOrder::TIME_SLOTS),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options(WorkOrder::allStatusOptions())
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Checklist de trabajo')
                    ->description('Heredado de los ítems cargados en el Relevamiento — tildá cada uno a medida que se completa.')
                    ->schema([
                        Forms\Components\Repeater::make('checklistItems')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Forms\Components\Textarea::make('description')
                                    ->label('Descripción')
                                    ->rows(2),
                                Forms\Components\Textarea::make('observations')
                                    ->label('Observaciones')
                                    ->rows(2),
                                Forms\Components\Toggle::make('includes_pickup')
                                    ->label('Incluye retiro')
                                    ->helperText('Heredado del relevamiento — no editable acá.')
                                    ->disabled(),
                                Forms\Components\Toggle::make('is_completed')
                                    ->label('Completado'),
                            ])
                            ->columns(2)
                            ->orderColumn('order')
                            ->addable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['description'] ?? null),
                    ]),

                Forms\Components\Section::make('Fotos del trabajo')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('before_photos')
                            ->collection('before_photos')
                            ->label('Antes')
                            ->multiple()
                            ->image()
                            ->imageEditor()
                            ->reorderable()
                            ->openable(),
                        Forms\Components\SpatieMediaLibraryFileUpload::make('after_photos')
                            ->collection('after_photos')
                            ->label('Después')
                            ->multiple()
                            ->image()
                            ->imageEditor()
                            ->reorderable()
                            ->openable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('work_order_number')
                    ->label('N° Orden de Trabajo')
                    ->searchable(query: fn ($query, string $search) => $query->whereHas(
                        'serviceOrder',
                        fn ($query) => $query->whereDocumentNumberLike($search),
                    ))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('serviceOrder.customer.name')
                    ->label('Cliente')
                    ->default('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('serviceOrder.property.display_label')
                    ->label('Propiedad')
                    ->default('—'),
                Tables\Columns\TextColumn::make('serviceOrder.category.name')
                    ->label('Categoría')
                    ->default('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nueva' => 'gray',
                        'programado' => 'info',
                        'en_curso' => 'warning',
                        'completado' => 'success',
                        'cancelado' => 'danger',
                        'reprogramado' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => (WorkOrder::PIPELINE_STATUSES + WorkOrder::OTHER_STATUSES)[$state] ?? $state),
                Tables\Columns\TextColumn::make('work_date')
                    ->label('Fecha')
                    ->getStateUsing(fn (WorkOrder $record) => $record->status === 'programado' ? $record->work_date : null)
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('serviceOrder.customer', 'serviceOrder.property', 'serviceOrder.category'))
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(WorkOrder::allStatusOptions()),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkOrders::route('/'),
            'edit' => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }

    protected static function pendingAttentionCount(): int
    {
        return static::getModel()::where('status', 'en_curso')
            ->whereNull('conformity_sent_at')
            ->count();
    }

    protected static function pendingAttentionTooltip(): ?string
    {
        return 'Trabajos en curso a los que todavía no se les pidió conformidad al cliente';
    }
}
