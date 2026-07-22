<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages;
use App\Models\BankAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Relevamientos';

    protected static ?string $navigationLabel = 'Cuentas Bancarias';

    protected static ?string $modelLabel = 'cuenta bancaria';

    protected static ?string $pluralModelLabel = 'cuentas bancarias';

    // Después de Relevamientos (1) y Herramientas de Trabajo (2), al final del grupo.
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre / alias descriptivo')
                    ->helperText('Para identificarla en el listado, ej: "Banco Galicia - Pesos"')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bank_name')
                    ->label('Banco')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('account_holder')
                    ->label('Titular')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('cbu')
                    ->label('CBU')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('cbu_alias')
                    ->label('Alias de CBU')
                    ->maxLength(255),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Banco')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_holder')
                    ->label('Titular')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cbu')
                    ->label('CBU')
                    ->copyable(),
                Tables\Columns\TextColumn::make('cbu_alias')
                    ->label('Alias de CBU')
                    ->copyable()
                    ->default('—'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'edit' => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
