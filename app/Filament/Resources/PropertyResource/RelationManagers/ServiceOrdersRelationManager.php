<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use App\Filament\Resources\ServiceOrderResource;
use App\Models\ServiceOrder;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceOrders';

    protected static ?string $title = 'Órdenes de servicio';

    protected static ?string $modelLabel = 'orden de servicio';

    /**
     * Reutiliza los campos de ServiceOrderResource (sin customer_id ni
     * property_id, que acá ya vienen dados por la Property dueña de esta
     * pestaña) para que este form no se desincronice del principal — ver
     * el comentario en ServiceOrderResource::flowAndRelevamientoFields().
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ...ServiceOrderResource::flowAndRelevamientoFields(
                    relevamientoQueryModifier: fn ($query) => $query->where('property_id', $this->getOwnerRecord()->id),
                    relevamientoOptionLabel: fn ($record) => collect([
                        $record->relevador?->name,
                        $record->scheduled_date?->format('d/m/Y') ?? 'sin fecha',
                    ])->filter()->join(' — '),
                ),
                ...ServiceOrderResource::detailFields(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('budget_number')
                    ->label('N° Presupuesto')
                    ->searchable(query: fn ($query, string $search) => $query->whereDocumentNumberLike($search)),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => ServiceOrderResource::statusColor($state))
                    ->formatStateUsing(fn (string $state): string => ServiceOrderResource::statusLabel($state)),
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
