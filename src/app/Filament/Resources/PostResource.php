<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Principal')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('slug', Str::slug($state));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Se genera automáticamente desde el título'),
                        Forms\Components\TextInput::make('subtitle')
                            ->label('Subtítulo')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contenido')
                    ->schema([
                        Forms\Components\Textarea::make('excerpt')
                            ->label('Extracto/Resumen')
                            ->maxLength(65535)
                            ->helperText('Breve resumen que aparece en listados'),
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenido')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('posts/attachments')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Detalles del Trabajo')
                    ->schema([
                        Forms\Components\TextInput::make('location')
                            ->label('Ubicación')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('client_name')
                            ->label('Nombre del cliente')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('project_size')
                            ->label('Tamaño del proyecto')
                            ->maxLength(255)
                            ->placeholder('Ej: 5000 m²'),
                        Forms\Components\TextInput::make('project_duration')
                            ->label('Duración')
                            ->maxLength(255)
                            ->placeholder('Ej: 3 días'),
                        Forms\Components\TextInput::make('machinery_used')
                            ->label('Maquinaria utilizada')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Imágenes')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->label('Imagen destacada')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/jfif', 'image/bmp', 'image/gif'])
                            ->directory('posts/featured')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->helperText('Formatos aceptados: JPG, PNG, WEBP, JFIF, BMP, GIF'),
                        Forms\Components\Toggle::make('has_before_after')
                            ->label('Tiene imágenes antes/después')
                            ->default(false),
                        Forms\Components\FileUpload::make('gallery_images')
                            ->label('Galería de imágenes')
                            ->multiple()
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/jfif', 'image/bmp', 'image/gif'])
                            ->directory('posts/gallery')
                            ->visibility('public')
                            ->imageEditor()
                            ->reorderable()
                            ->openable()
                            ->columnSpanFull()
                            ->helperText('Formatos aceptados: JPG, PNG, WEBP, JFIF, BMP, GIF. Puede seleccionar múltiples imágenes.'),
                    ]),

                Forms\Components\Section::make('Etiquetas')
                    ->schema([
                        Forms\Components\Select::make('tags')
                            ->label('Tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ]),

                Forms\Components\Section::make('Publicación y SEO')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('Publicado')
                            ->default(true),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Destacado')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Fecha de publicación')
                            ->default(now()),
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Título SEO')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('meta_description')
                            ->label('Descripción SEO')
                            ->maxLength(65535),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('Imagen')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Ubicación')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publicado')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado el')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Publicados')
                    ->falseLabel('Borradores'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Destacados'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}