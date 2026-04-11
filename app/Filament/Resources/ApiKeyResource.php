<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiKeyResource\Pages;
use App\Models\ApiKey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'API Keys';
    protected static ?string $modelLabel = 'API Key';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Telegram Bot, WhatsApp Bot'),
            Forms\Components\TextInput::make('key')
                ->required()
                ->maxLength(64)
                ->default(fn () => Str::random(64))
                ->unique(ignoreRecord: true)
                ->placeholder('Auto-generated'),
            Forms\Components\Select::make('platform')
                ->options([
                    'telegram' => 'Telegram',
                    'whatsapp' => 'WhatsApp',
                    'other' => 'Other',
                ])
                ->required()
                ->default('other'),
            Forms\Components\Toggle::make('is_active')
                ->default(true)
                ->label('Aktif'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('No')->rowIndex()->label('#'),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('key')
                    ->copyable()
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->key),
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'telegram' => 'info',
                        'whatsapp' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('last_used_at')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Belum pernah'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiKeys::route('/'),
            'create' => Pages\CreateApiKey::route('/create'),
            'edit' => Pages\EditApiKey::route('/{record}/edit'),
        ];
    }
}
