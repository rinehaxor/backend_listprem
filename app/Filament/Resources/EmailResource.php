<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailResource\Pages;
use App\Models\Email;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailResource extends Resource
{
    protected static ?string $model = Email::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Email';
    protected static ?string $modelLabel = 'Email';
    protected static ?string $pluralModelLabel = 'Email';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('akun')
                ->required()
                ->maxLength(255)
                ->email()
                ->placeholder('email@gmail.com'),
            Forms\Components\TextInput::make('password')
                ->required()
                ->maxLength(255)
                ->placeholder('password123'),
            Forms\Components\TextInput::make('keterangan')
                ->required()
                ->maxLength(255)
                ->placeholder('Email utama, Email cadangan, dll'),
            Forms\Components\Select::make('source')
                ->options([
                    'dashboard' => 'Dashboard',
                    'telegram' => 'Telegram',
                    'whatsapp' => 'WhatsApp',
                ])
                ->default('dashboard')
                ->required(),
            Forms\Components\TextInput::make('source_user')
                ->maxLength(255)
                ->label('Diinput oleh'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('row_number')->rowIndex()->label('#'),
                Tables\Columns\TextColumn::make('akun')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('password')
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('keterangan')->searchable(),
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'telegram' => 'Telegram',
                        'whatsapp' => 'WhatsApp',
                        'dashboard' => 'Dashboard',
                    ]),
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
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmails::route('/'),
            'create' => Pages\CreateEmail::route('/create'),
            'edit' => Pages\EditEmail::route('/{record}/edit'),
        ];
    }
}
