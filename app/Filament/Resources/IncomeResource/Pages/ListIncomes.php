<?php
namespace App\Filament\Resources\IncomeResource\Pages;

use App\Filament\Resources\IncomeResource;
use App\Models\Income;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListIncomes extends ListRecords
{
    protected static string $resource = IncomeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importJson')
                ->label('Import JSON')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->form([
                    FileUpload::make('file')
                        ->label('Upload File JSON')
                        ->acceptedFileTypes(['application/json'])
                        ->storeFiles(true)
                        ->helperText('Atau paste text JSON langsung di bawah ini:'),
                    \Filament\Forms\Components\Textarea::make('json_text')
                        ->label('Raw JSON Text')
                        ->rows(8)
                        ->placeholder('[{"aplikasi": "CapCut", "jenis": "1 bulan", "laba": 10000}]'),
                ])
                ->action(function (array $data) {
                    $json = '';
                    if (!empty($data['file'])) {
                        $filePath = Storage::path($data['file']);
                        $json = file_get_contents($filePath);
                    } elseif (!empty($data['json_text'])) {
                        $json = $data['json_text'];
                    } else {
                        Notification::make()->title('Gagal')->body('Silakan upload file atau paste text JSON.')->danger()->send();
                        return;
                    }

                    $records = json_decode($json, true);

                    if (!$records || !is_array($records)) {
                        Notification::make()
                            ->title('Gagal')
                            ->body('Format file JSON tidak valid.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $success = 0;
                    foreach ($records as $row) {
                        try {
                            $laba = $row['laba'] ?? 0;
                            if (!is_numeric($laba)) {
                                $laba = (int) preg_replace('/[^0-9]/', '', (string)$laba);
                            }

                            Income::create([
                                'tanggal' => isset($row['tanggal']) ? Carbon::parse($row['tanggal']) : Carbon::today(),
                                'aplikasi' => $row['aplikasi'] ?? 'Unknown',
                                'jenis' => $row['jenis'] ?? '-',
                                'laba' => $laba,
                                'source' => $row['source'] ?? 'dashboard',
                                'source_user' => $row['source_user'] ?? 'Import Dashboard',
                                'created_at' => isset($row['tanggal']) ? Carbon::parse($row['tanggal']) : now(),
                            ]);
                            $success++;
                        } catch (\Exception $e) {
                            // bypass error row
                        }
                    }

                    Notification::make()
                        ->title('Import Selesai')
                        ->body("Berhasil mengimpor {$success} data Pemasukan.")
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
