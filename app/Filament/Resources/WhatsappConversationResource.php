<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPendingAttentionBadge;
use App\Filament\Resources\WhatsappConversationResource\Pages;
use App\Models\WhatsappConversation;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WhatsappConversationResource extends Resource
{
    use HasPendingAttentionBadge;

    protected static ?string $model = WhatsappConversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationLabel = 'WhatsApp (Claudia)';

    protected static ?string $modelLabel = 'conversación';

    protected static ?string $pluralModelLabel = 'conversaciones de WhatsApp';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última actividad')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('atender')
                    ->label('')
                    ->state('Atender')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('primary')
                    ->url(fn (WhatsappConversation $record): string => static::getUrl('manage', ['record' => $record])),
                Tables\Columns\BadgeColumn::make('estado_conversacion')
                    ->label('Estado')
                    ->colors([
                        'gray' => 'claudia_atendiendo',
                        'warning' => fn ($state): bool => in_array($state, ['esperando_agenda_visita', 'esperando_cotizacion_foto']),
                        'danger' => 'con_humano',
                        'success' => 'cerrada',
                    ])
                    ->formatStateUsing(fn (string $state): string => WhatsappConversation::ESTADOS[$state] ?? $state),
                Tables\Columns\TextColumn::make('customer.phone')
                    ->label('Teléfono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sitio_origen')
                    ->label('Sitio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('zona')
                    ->label('Zona')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('servicio_solicitado')
                    ->label('Servicio')
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado_conversacion')
                    ->label('Estado')
                    ->options(WhatsappConversation::ESTADOS),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsappConversations::route('/'),
            'manage' => Pages\ManageConversation::route('/{record}/atender'),
        ];
    }

    protected static function pendingAttentionCount(): int
    {
        return static::getModel()::where('estado_conversacion', 'con_humano')->count();
    }

    protected static function pendingAttentionTooltip(): ?string
    {
        return 'Conversaciones derivadas a un humano, esperando respuesta';
    }

    protected static function pendingAttentionColor(): string
    {
        return 'danger';
    }
}
