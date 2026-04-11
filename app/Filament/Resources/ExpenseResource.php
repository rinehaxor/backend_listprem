<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Pengeluaran';
    protected static ?string $modelLabel = 'Pengeluaran';
    protected static ?string $pluralModelLabel = 'Pengeluaran';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('tanggal')
                ->required()
                ->default(now())
                ->native(false)
                ->displayFormat('d/m/Y'),
            Forms\Components\TextInput::make('kategori')
                ->required()
                ->maxLength(255)
                ->placeholder('Makan, Akun, Transport, dll')
                ->datalist([
                    'Makan', 'Akun', 'Transport', 'Tagihan', 'Belanja', 'Lainnya',
                ]),
            Forms\Components\TextInput::make('keterangan')
                ->required()
                ->maxLength(255)
                ->placeholder('Deskripsi pengeluaran'),
            Forms\Components\TextInput::make('nominal')
                ->required()
                ->numeric()
                ->prefix('Rp')
                ->placeholder('15000'),
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
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('tanggal')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('kategori')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'makan' => 'warning',
                        'akun' => 'info',
                        'transport' => 'success',
                        'tagihan' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('keterangan')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR', locale: 'id')),
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->options(fn () => Expense::distinct()->pluck('kategori', 'kategori')->toArray()),
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
