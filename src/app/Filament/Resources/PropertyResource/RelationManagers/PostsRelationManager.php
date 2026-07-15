<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    protected static bool $isLazy = false;

    protected static ?string $title = 'Trabajos / Posts relacionados';

    protected static ?string $modelLabel = 'post';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('relation_type')
                    ->label('Tipo de relación')
                    ->options([
                        'trabajo_realizado' => 'Trabajo realizado',
                        'comentario' => 'Comentario',
                        'testimonio' => 'Testimonio',
                    ])
                    ->default('trabajo_realizado')
                    ->required(),
                Forms\Components\Textarea::make('comment')
                    ->label('Comentario')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('rating')
                    ->label('Calificación')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5),
                Forms\Components\DatePicker::make('service_date')
                    ->label('Fecha del servicio'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Post')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría'),
                Tables\Columns\TextColumn::make('pivot.relation_type')
                    ->label('Tipo de relación')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'trabajo_realizado' => 'Trabajo realizado',
                        'comentario' => 'Comentario',
                        'testimonio' => 'Testimonio',
                        default => $state ?? '—',
                    }),
                Tables\Columns\TextColumn::make('pivot.rating')
                    ->label('Calificación'),
                Tables\Columns\TextColumn::make('pivot.service_date')
                    ->label('Fecha del servicio')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('pivot.comment')
                    ->label('Comentario')
                    ->limit(40),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Vincular post')
                    ->recordSelectSearchColumns(['title'])
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('relation_type')
                            ->label('Tipo de relación')
                            ->options([
                                'trabajo_realizado' => 'Trabajo realizado',
                                'comentario' => 'Comentario',
                                'testimonio' => 'Testimonio',
                            ])
                            ->default('trabajo_realizado')
                            ->required(),
                        Forms\Components\Textarea::make('comment')
                            ->label('Comentario')
                            ->maxLength(1000),
                        Forms\Components\TextInput::make('rating')
                            ->label('Calificación')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5),
                        Forms\Components\DatePicker::make('service_date')
                            ->label('Fecha del servicio'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar relación'),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
