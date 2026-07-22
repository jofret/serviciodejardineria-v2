<?php

namespace App\Filament\Resources\ServiceOrderResource\Pages;

use App\Filament\Resources\ServiceOrderResource;
use App\Filament\Resources\WorkOrderResource;
use App\Models\ServiceOrder;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceOrder extends ViewRecord
{
    protected static string $resource = ServiceOrderResource::class;

    public function getTitle(): string
    {
        return 'Orden de servicio';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit_observations')
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

            Actions\Action::make('budget_status_sent')
                ->label('Presupuestado y Enviado')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->disabled()
                ->visible(fn (ServiceOrder $record): bool => $record->status === 'presupuestado_enviado'),

            Actions\Action::make('budget_status_accepted')
                ->label('Presupuestado y Aceptado')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->disabled()
                ->visible(fn (ServiceOrder $record): bool => $record->status === 'presupuesto_aceptado'),

            Actions\Action::make('review_and_quote')
                ->label('Revisar y presupuestar')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->visible(fn (ServiceOrder $record): bool => $record->canReviewAndQuote() && ! in_array($record->status, ['presupuestado_enviado', 'presupuesto_aceptado'], true))
                ->url(fn (ServiceOrder $record): string => ServiceOrderResource::getUrl('review-and-quote', ['record' => $record])),

            Actions\Action::make('view_work_order')
                ->label('Ver orden de trabajo')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('gray')
                ->visible(fn (ServiceOrder $record): bool => $record->workOrder !== null)
                ->url(fn (ServiceOrder $record): string => WorkOrderResource::getUrl('edit', ['record' => $record->workOrder])),

            // Cubre las órdenes que ya estaban en "Presupuesto aceptado" antes
            // de que existiera la generación automática (generateWorkOrder()
            // solo se dispara dentro de acceptBudget(), que es idempotente y
            // no vuelve a correr para una orden ya aceptada).
            Actions\Action::make('generate_work_order')
                ->label('Generar orden de trabajo')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('primary')
                ->visible(fn (ServiceOrder $record): bool => $record->status === 'presupuesto_aceptado' && $record->workOrder === null)
                ->requiresConfirmation()
                ->modalHeading('Generar orden de trabajo')
                ->modalDescription('Se crea la Orden de Trabajo para esta orden, con el checklist heredado del Relevamiento vinculado (si lo tiene).')
                ->modalSubmitActionLabel('Generar ahora')
                ->action(function (ServiceOrder $record): void {
                    $record->generateWorkOrder();

                    Notification::make()
                        ->title('Orden de trabajo generada')
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make()
                ->modalDescription(fn (ServiceOrder $record): ?string => ServiceOrderResource::deleteWarning($record)),
        ];
    }
}
