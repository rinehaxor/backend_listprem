<?php
namespace App\Filament\Resources\EmailResource\Pages;

use App\Filament\Resources\EmailResource;
use App\Models\Email;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListEmails extends ListRecords
{
    protected static string $resource = EmailResource::class;

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
                        ->placeholder('[{"akun": "user1@gmail.com", "password": "pass123", "keterangan": "Email utama"}, {"akun": "user2@gmail.com", "password": "pass456", "keterangan": "Email cadangan"}]')
                        ->helperText('Contoh format: [{"akun": "email@gmail.com", "password": "pass123", "keterangan": "Email utama"}]'),
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
                            Email::create([
                                'akun' => $row['akun'] ?? $row['email'] ?? 'Unknown',
                                'password' => $row['password'] ?? '',
                                'keterangan' => $row['keterangan'] ?? '-',
                                'source' => $row['source'] ?? 'dashboard',
                                'source_user' => $row['source_user'] ?? 'Import Dashboard',
                            ]);
                            $success++;
                        } catch (\Exception $e) {
                            // bypass error row
                        }
                    }

                    Notification::make()
                        ->title('Import Selesai')
                        ->body("Berhasil mengimpor {$success} data Email.")
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make()
        ];
    }
}
