<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RelevamientoResource\Pages;
use App\Models\Relevamiento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class RelevamientoResource extends Resource
{
    protected static ?string $model = Relevamiento::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Relevamientos';

    protected static ?string $navigationLabel = 'Relevamientos';

    protected static ?string $modelLabel = 'relevamiento';

    protected static ?string $pluralModelLabel = 'relevamientos';

    public static function normalizeCategoryData(array $data): array
    {
        if (($data['category_id'] ?? null) === 'otro') {
            $data['category_id'] = null;
        } else {
            $data['category_other'] = null;
        }

        return $data;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('property_id')
                    ->label('Propiedad')
                    ->relationship('property', 'address')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->customer?->name.' — '.$record->display_label)
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->label('Tipo de servicio')
                    ->options(fn () => \App\Models\Category::query()->orderBy('order')->pluck('name', 'id')->put('otro', 'Otro'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),
                Forms\Components\TextInput::make('category_other')
                    ->label('Especificar tipo de servicio')
                    ->maxLength(255)
                    ->visible(fn (callable $get): bool => $get('category_id') === 'otro')
                    ->required(fn (callable $get): bool => $get('category_id') === 'otro'),
                Forms\Components\Select::make('assigned_to')
                    ->label('Relevador asignado')
                    ->relationship('relevador', 'name', fn ($query) => $query->where('role', 'relevador'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('scheduled_date')
                    ->label('Fecha programada'),
                Forms\Components\TimePicker::make('scheduled_time_from')
                    ->label('Hora desde')
                    ->seconds(false),
                Forms\Components\TimePicker::make('scheduled_time_to')
                    ->label('Hora hasta')
                    ->seconds(false),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'enviado_a_relevador' => 'Enviado a relevador',
                    ])
                    ->default('pendiente')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('photos')
                    ->collection('photos')
                    ->label('Fotos cargadas por el relevador')
                    ->disabled()
                    ->image()
                    ->columnSpanFull()
                    ->visible(fn (string $operation): bool => $operation !== 'create'),
            ]);
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
                    ->label('Propiedad'),
                Tables\Columns\TextColumn::make('service_type_label')
                    ->label('Tipo de servicio')
                    ->default('—'),
                Tables\Columns\TextColumn::make('relevador.name')
                    ->label('Relevador')
                    ->default('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_time_from')
                    ->label('Horario')
                    ->formatStateUsing(fn ($state, $record) => $state
                        ? Carbon::parse($state)->format('H:i').($record->scheduled_time_to ? ' - '.Carbon::parse($record->scheduled_time_to)->format('H:i') : '')
                        : '—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'enviado_a_relevador' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendiente' => 'Pendiente',
                        'enviado_a_relevador' => 'Enviado a relevador',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('submitted_at')
                    ->label('Completado por relevador')
                    ->boolean()
                    ->getStateUsing(fn (Relevamiento $record): bool => $record->submitted_at !== null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'enviado_a_relevador' => 'Enviado a relevador',
                    ]),
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Relevador')
                    ->relationship('relevador', 'name', fn ($query) => $query->where('role', 'relevador')),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Tipo de servicio')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('enviar')
                    ->label('Enviar a relevador')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Relevamiento $record): bool => $record->status === 'pendiente')
                    ->requiresConfirmation()
                    ->modalHeading('Enviar relevamiento a relevador')
                    ->modalDescription('Revisá que la propiedad, el relevador asignado y la fecha/horario estén correctos antes de confirmar. Al enviarlo, el relevador va a poder verlo y completarlo desde su panel.')
                    ->modalSubmitActionLabel('Confirmar envío')
                    ->action(fn (Relevamiento $record) => $record->update(['status' => 'enviado_a_relevador']))
                    ->successNotificationTitle('Relevamiento enviado a relevador'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_date', 'desc');
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
            'index' => Pages\ListRelevamientos::route('/'),
            'create' => Pages\CreateRelevamiento::route('/create'),
            'edit' => Pages\EditRelevamiento::route('/{record}/edit'),
        ];
    }
}
