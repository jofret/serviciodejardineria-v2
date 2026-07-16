<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('zone')
                            ->label('Zona')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Datos adicionales')
                    ->schema([
                        Forms\Components\DatePicker::make('birthday')
                            ->label('Cumpleaños'),
                        Forms\Components\Select::make('customer_type')
                            ->label('Tipo de cliente')
                            ->options([
                                'casa' => 'Casa particular',
                                'empresa' => 'Empresa',
                                'consorcio' => 'Consorcio',
                                'country' => 'Country/Barrio privado',
                                'otro' => 'Otro',
                            ])
                            ->default('casa'),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'activo' => 'Activo',
                                'inactivo' => 'Inactivo',
                                'potencial' => 'Potencial',
                            ])
                            ->default('potencial'),
                        Forms\Components\Select::make('lead_status')
                            ->label('Etapa del lead')
                            ->options([
                                'nuevo' => 'Nuevo',
                                'contactado' => 'Contactado',
                                'visitado' => 'Visitado',
                                'presupuestado' => 'Presupuestado',
                                'cliente' => 'Cliente',
                                'descartado' => 'Descartado',
                            ])
                            ->default('nuevo')
                            ->required(),
                        Forms\Components\Select::make('preferred_contact')
                            ->label('Contacto preferido')
                            ->options([
                                'whatsapp' => 'WhatsApp',
                                'email' => 'Email',
                                'telefono' => 'Teléfono',
                            ])
                            ->default('whatsapp'),
                    ])->columns(2),

                Forms\Components\Section::make('Notas')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas internas')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadatos adicionales')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('zone')
                    ->label('Zona'),
                Tables\Columns\TextColumn::make('customer_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => [
                        'casa' => 'Casa',
                        'empresa' => 'Empresa',
                        'consorcio' => 'Consorcio',
                        'country' => 'Country',
                        'otro' => 'Otro',
                    ][$state] ?? $state),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'activo',
                        'warning' => 'potencial',
                        'danger' => 'inactivo',
                    ])
                    ->formatStateUsing(fn (string $state): string => [
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                        'potencial' => 'Potencial',
                    ][$state] ?? $state),
                Tables\Columns\TextColumn::make('lead_status')
                    ->label('Etapa del lead')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nuevo' => 'info',
                        'contactado', 'visitado' => 'warning',
                        'presupuestado' => 'primary',
                        'cliente' => 'success',
                        'descartado' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'nuevo' => 'Nuevo',
                        'contactado' => 'Contactado',
                        'visitado' => 'Visitado',
                        'presupuestado' => 'Presupuestado',
                        'cliente' => 'Cliente',
                        'descartado' => 'Descartado',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('properties_count')
                    ->label('Propiedades')
                    ->counts('properties')
                    ->sortable(),
                Tables\Columns\TextColumn::make('testimonial_status')
                    ->label('Testimonio')
                    ->badge()
                    ->getStateUsing(fn (Customer $record): string => $record->testimonialStatusLabel())
                    ->color(fn (string $state): string => match ($state) {
                        'No enviado' => 'gray',
                        'Enlace enviado' => 'warning',
                        'Completado' => 'info',
                        'Publicado' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                        'potencial' => 'Potencial',
                    ]),
                Tables\Filters\SelectFilter::make('lead_status')
                    ->label('Etapa del lead')
                    ->options([
                        'nuevo' => 'Nuevo',
                        'contactado' => 'Contactado',
                        'visitado' => 'Visitado',
                        'presupuestado' => 'Presupuestado',
                        'cliente' => 'Cliente',
                        'descartado' => 'Descartado',
                    ]),
                Tables\Filters\SelectFilter::make('customer_type')
                    ->label('Tipo de cliente')
                    ->options([
                        'casa' => 'Casa particular',
                        'empresa' => 'Empresa',
                        'consorcio' => 'Consorcio',
                        'country' => 'Country',
                    ]),
                Tables\Filters\SelectFilter::make('zone')
                    ->label('Zona'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                // Botón para enviar encuesta por WhatsApp
                Tables\Actions\Action::make('sendSurvey')
                    ->label('📱 Encuesta WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->visible(fn (Customer $record): bool => $record->canRequestTestimonial())
                    ->requiresConfirmation()
                    ->modalHeading('Enviar encuesta por WhatsApp')
                    ->modalDescription('Se enviará un enlace al cliente para que complete su opinión. Los datos del cliente se precargarán automáticamente.')
                    ->modalSubmitActionLabel('Enviar ahora')
                    ->action(function ($record) {
                        // Generar token único
                        $token = md5($record->id . time() . rand(1000, 9999));
                        
                        // Crear la encuesta
                        $survey = \App\Models\Survey::create([
                            'customer_id' => $record->id,
                            'token' => $token,
                            'sent_at' => now(),
                            'comment' => '',
                        ]);
                        
                        // Crear el enlace
                        $enlace = url('/encuesta/' . $token);
                        
                        // Preparar número de teléfono
                        $telefono = preg_replace('/[^0-9]/', '', $record->phone);
                        
                        // Formato para Argentina
                        if (substr($telefono, 0, 1) == '0') {
                            $telefono = '54' . substr($telefono, 1);
                        } elseif (substr($telefono, 0, 2) != '54') {
                            $telefono = '54' . $telefono;
                        }
                        
                        // Mensaje personalizado
                        $mensaje = "Hola {$record->name}! 👋\n\n";
                        $mensaje .= "Gracias por confiar en *Servicio de Jardinería*. Queremos conocer tu opinión sobre el servicio que recibiste.\n\n";
                        $mensaje .= "📝 Por favor, completá esta breve encuesta:\n";
                        $mensaje .= $enlace . "\n\n";
                        $mensaje .= "¡Tu opinión nos ayuda a mejorar! 🌿";
                        
                        // Codificar mensaje
                        $mensajeCodificado = urlencode($mensaje);

                        // Crear enlace de WhatsApp: se usa api.whatsapp.com/send directo en vez de
                        // wa.me, porque el acortador wa.me corrompe emojis (4 bytes UTF-8) en su
                        // propio redirect hacia api.whatsapp.com.
                        $whatsappLink = "https://api.whatsapp.com/send/?phone={$telefono}&text={$mensajeCodificado}&type=phone_number&app_absent=0";
                        
                        // Notificación en el panel
                        \Filament\Notifications\Notification::make()
                            ->title('✅ Enlace generado')
                            ->body("Hacé clic para enviar por WhatsApp")
                            ->success()
                            ->send();
                        
                        // Abrir WhatsApp en una nueva pestaña
                        return redirect($whatsappLink);
                    }),
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
            \App\Filament\Resources\CustomerResource\RelationManagers\PropertiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}