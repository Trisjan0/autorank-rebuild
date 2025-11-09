<?php

namespace App\Filament\Traits;

use App\Services\GoogleDriveService;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Exceptions\GoogleAccountDisconnectedException;
use Filament\Notifications\Notification;

trait HandlesKRAFileUploads
{
    /**
     * Returns a FileUpload component configured for Google Drive
     * using the per-user GoogleDriveService.
     */
    protected function getKRAFileUploadComponent(): FileUpload
    {
        return FileUpload::make('google_drive_file_id')
            ->label('Proof Document(s)')
            ->helperText('Upload proof documents (PDF, DOCX, XLSX, JPG, PNG, or ZIP, max 10MB each).')
            ->multiple()
            ->reorderable()
            ->required()
            ->maxSize(10240) // 10MB
            ->acceptedFileTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                'application/msword', // .doc
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                'application/vnd.ms-excel', // .xls
                'application/zip',
            ])
            ->preserveFilenames()
            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, $livewire): string {

                try {
                    $service = app(GoogleDriveService::class)->forUser(Auth::user());

                    $folderPath = $livewire->getGoogleDriveFolderPath();

                    return $service->uploadFile($file, $folderPath);
                } catch (GoogleAccountDisconnectedException $e) {
                    Notification::make()
                        ->title('Google Drive Error')
                        ->body('Your Google account is not connected or has revoked permission. Please log out and log back in to reconnect.')
                        ->danger()
                        ->send();
                    throw new \Exception('Google Drive Error: ' . $e->getMessage());
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Upload Failed')
                        ->body('An unexpected error occurred while uploading the file: ' . $e->getMessage())
                        ->danger()
                        ->send();

                    throw new \Exception('Upload Failed: ' . $e->getMessage());
                }
            })
            ->deleteUploadedFileUsing(function (string $fileIdentifier) {
                try {
                    $service = app(GoogleDriveService::class)->forUser(Auth::user());
                    $service->deleteFile($fileIdentifier);
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Delete Failed')
                        ->body('Could not delete the file from Google Drive: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->columnSpanFull();
    }
}
