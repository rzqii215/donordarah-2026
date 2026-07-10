<?php

namespace App\Filament\Admin\Resources\PemeriksaanKesehatanResource\Pages;

use App\Enums\StatusPendaftaranDonor;
use App\Filament\Admin\Resources\PemeriksaanKesehatanResource;
use App\Filament\Admin\Resources\PendaftaranDonorResource;
use App\Models\PemeriksaanKesehatan;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPemeriksaanKesehatan extends ViewRecord
{
    protected static string $resource =
        PemeriksaanKesehatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Pemeriksaan')
                ->visible(function (): bool {
                    return $this->record instanceof
                        PemeriksaanKesehatan
                        && $this->bolehDiubah($this->record);
                }),

            Actions\Action::make('lihat_pendaftaran')
                ->label('Lihat Pendaftaran')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('gray')
                ->visible(function (): bool {
                    return $this->record instanceof
                        PemeriksaanKesehatan
                        && $this->record
                            ->pendaftaran_donor_id !== null;
                })
                ->url(function (): string {
                    return PendaftaranDonorResource::getUrl(
                        'view',
                        [
                            'record' =>
                                $this->record
                                    ->pendaftaran_donor_id,
                        ]
                    );
                }),
        ];
    }

    private function bolehDiubah(
        PemeriksaanKesehatan $record
    ): bool {
        $pendaftaran = $record->pendaftaran;

        if ($pendaftaran === null) {
            return false;
        }

        if ($pendaftaran->kantongDarah()->exists()) {
            return false;
        }

        $status =
            $pendaftaran->status instanceof StatusPendaftaranDonor
                ? $pendaftaran->status
                : StatusPendaftaranDonor::from(
                    (string) $pendaftaran->status
                );

        return $status === StatusPendaftaranDonor::Hadir;
    }
}