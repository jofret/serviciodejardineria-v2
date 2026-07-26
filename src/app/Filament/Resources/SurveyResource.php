<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\SendsSurveyThanks;
use App\Filament\Resources\SurveyResource\Pages;
use App\Models\Survey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SurveyResource extends Resource
{
    use SendsSurveyThanks;

    protected static ?string $model = Survey::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationLabel = 'Encuestas';

    protected static ?string $modelLabel = 'encuesta';

    protected static ?string $pluralModelLabel = 'encuestas';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Origen')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('post_id')
                            ->label('Post relacionado')
                            ->relationship('post', 'title')
                            ->searchable()
                            ->preload()
                            ->required(fn (callable $get): bool => (bool) $get('is_published'))
                            ->helperText('Obligatorio para poder publicar la encuesta.'),
                        Forms\Components\TextInput::make('token')
                            ->label('Token')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Identificador único del enlace enviado por WhatsApp'),
                    ])->columns(2),

                Forms\Components\Section::make('Respuesta del cliente')
                    ->schema([
                        Forms\Components\TextInput::make('gender')
                            ->label('Género')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('occupation')
                            ->label('Ocupación')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('birthday_month')
                            ->label('Mes de cumpleaños')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('birthday_day')
                            ->label('Día de cumpleaños')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('comment')
                            ->label('Comentario / Testimonio')
                            ->required()
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Moderación')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('Publicada en el sitio')
                            ->live()
                            ->helperText('Activalo solo después de revisar el comentario.'),
                        Forms\Components\DateTimePicker::make('sent_at')
                            ->label('Enviada el')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('answered_at')
                            ->label('Respondida el')
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post relacionado')
                    ->limit(30)
                    ->default('—'),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Comentario')
                    ->limit(50)
                    ->default('Sin responder')
                    ->searchable(),
                Tables\Columns\IconColumn::make('answered_at')
                    ->label('Respondida')
                    ->boolean()
                    ->getStateUsing(fn (Survey $record): bool => filled($record->answered_at)),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publicada')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Enviada')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publicada'),
                Tables\Filters\Filter::make('answered')
                    ->label('Respondidas')
                    ->query(fn ($query) => $query->whereNotNull('answered_at')),
                Tables\Filters\Filter::make('pending')
                    ->label('Pendientes de respuesta')
                    ->query(fn ($query) => $query->whereNull('answered_at')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Revisar'),
                Tables\Actions\Action::make('agradecerPorWhatsapp')
                    ->label('Agradecer por WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->visible(fn (Survey $record): bool => $record->is_published && filled($record->customer?->phone))
                    ->extraAttributes(static::whatsAppTriggerAttributes())
                    ->action(fn (Survey $record, $livewire) => static::sendSurveyThanks($record, $livewire, includeEmail: false)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSurveys::route('/'),
            'edit' => Pages\EditSurvey::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNotNull('answered_at')
            ->where('is_published', false)
            ->count() ?: null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Testimonios respondidos y pendientes de publicar';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
