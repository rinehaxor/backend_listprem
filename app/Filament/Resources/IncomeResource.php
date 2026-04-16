<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Models\Income;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IncomeResource extends Resource
{
    protected static ?string $model = Income::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Pemasukan';
    protected static ?string $modelLabel = 'Pemasukan';
    protected static ?string $pluralModelLabel = 'Pemasukan';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('tanggal')
                ->required()
                ->default(now())
                ->native(false)
                ->displayFormat('d/m/Y'),
            Forms\Components\TextInput::make('aplikasi')
                ->required()
                ->maxLength(255)
                ->placeholder('CapCut, Canva, Spotify, dll')
                ->datalist([
                    'CapCut', 'Canva', 'Spotify', 'Netflix',
                    'YouTube Premium', 'Alight Motion', 'Adobe',
                ]),
            Forms\Components\TextInput::make('jenis')
                ->required()
                ->maxLength(255)
                ->placeholder('1 bulan, 3 bulan, lifetime')
                ->datalist([
                    '1 bulan', '3 bulan', '6 bulan', '1 tahun', 'lifetime',
                ]),
            Forms\Components\TextInput::make('laba')
                ->required()
                ->numeric()
                ->prefix('Rp')
                ->placeholder('8000'),
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
                ->placeholder('Nama user')
                ->label('Diinput oleh'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('row_number')
                    ->rowIndex()
                    ->label('#'),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('aplikasi')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains(strtolower($state), 'capcut') => 'info',
                        str_contains(strtolower($state), 'canva') => 'success',
                        str_contains(strtolower($state), 'spotify') => 'warning',
                        str_contains(strtolower($state), 'netflix') => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('jenis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('laba')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR', locale: 'id')),
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'telegram' => 'info',
                        'whatsapp' => 'success',
                        'dashboard' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('bulan')
                    ->form([
                        Forms\Components\Select::make('bulan')
                            ->options([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                            ])
                            ->default(Carbon::now()->month)
                            ->label('Bulan'),
                        Forms\Components\Select::make('tahun')
                            ->options(fn () => collect(range(Carbon::now()->year, Carbon::now()->year - 2))
                                ->mapWithKeys(fn ($y) => [$y => $y])->toArray())
                            ->default(Carbon::now()->year)
                            ->label('Tahun'),
                    ])
                    ->query(function ($query, array $data) {
                        $bulan = $data['bulan'] ?? Carbon::now()->month;
                        $tahun = $data['tahun'] ?? Carbon::now()->year;
                        return $query
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun);
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $bulanNames = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
                        $bulan = $data['bulan'] ?? Carbon::now()->month;
                        $tahun = $data['tahun'] ?? Carbon::now()->year;
                        return ($bulanNames[(int)$bulan] ?? '') . ' ' . $tahun;
                    }),
                Tables\Filters\SelectFilter::make('aplikasi')
                    ->options(fn () => Income::distinct()->pluck('aplikasi', 'aplikasi')->toArray()),
                Tables\Filters\SelectFilter::make('jenis')
                    ->options(fn () => Income::distinct()->pluck('jenis', 'jenis')->toArray()),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncomes::route('/'),
            'create' => Pages\CreateIncome::route('/create'),
            'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }
}
