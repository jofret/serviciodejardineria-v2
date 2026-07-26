<?php

namespace App\Filament\Resources\SurveyResource\Pages;

use App\Filament\Concerns\SendsSurveyThanks;
use App\Filament\Resources\SurveyResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditSurvey extends EditRecord
{
    use SendsSurveyThanks;

    protected static string $resource = SurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->extraAttributes(static::whatsAppTriggerAttributes());
    }

    protected function afterSave(): void
    {
        $justPublished = $this->record->wasChanged('is_published') && $this->record->is_published;

        if (! $justPublished) {
            $this->js(static::closeWhatsAppTab());

            return;
        }

        static::sendSurveyThanks($this->record, $this);
    }
}
