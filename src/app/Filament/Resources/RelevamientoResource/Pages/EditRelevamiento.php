<?php

namespace App\Filament\Resources\RelevamientoResource\Pages;

use App\Filament\Resources\RelevamientoResource;
use App\Models\Relevamiento;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRelevamiento extends EditRecord
{
    protected static string $resource = RelevamientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve_reopen')
                ->label('Aprobar reapertura')
                ->icon('heroicon-o-lock-open')
                ->color('warning')
                ->visible(fn (Relevamiento $record): bool => $record->reopen_requested_at !== null)
                ->requiresConfirmation()
                ->modalHeading('Aprobar reapertura del relevamiento')
                ->modalDescription('El relevamiento vuelve a quedar editable para el relevador, que va a poder modificarlo y volver a enviarlo.')
                ->modalSubmitActionLabel('Aprobar reapertura')
                ->action(function (Relevamiento $record) {
                    $record->approveReopen();
                    $this->fillForm();
                })
                ->successNotificationTitle('Reapertura aprobada'),
            Actions\DeleteAction::make()
                ->modalDescription(fn (Relevamiento $record): ?string => RelevamientoResource::deleteWarning($record)),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['category_id']) && filled($data['category_other'] ?? null)) {
            $data['category_id'] = 'otro';
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return RelevamientoResource::normalizeCategoryData($data);
    }
}
