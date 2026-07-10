<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Pages;

use App\Enums\StatusPendaftaranDonor;
use App\Filament\Admin\Resources\KantongDarahResource;
use App\Filament\Admin\Resources\PemeriksaanKesehatanResource;
use App\Filament\Admin\Resources\PendaftaranDonorResource;
use App\Models\PendaftaranDonor;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPendaftaranDonor extends ViewRecord
{
    protected static string $resource = PendaftaranDonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Pendaftaran')
                ->visible(function (): bool {
                    return $this->record instanceof PendaftaranDonor
                        && PendaftaranDonorResource::canEdit($this->record);
                }),

            Actions\Action::make('buat_pemeriksaan_kesehatan')
                ->label('Buat Pemeriksaan')
                ->icon('heroicon-o-heart')
                ->color('primary')
                ->visible(function (): bool {
                    return $this->record instanceof PendaftaranDonor
                        && $this->record->status === StatusPendaftaranDonor::Hadir
                        && ! $this->record
                            ->pemeriksaanKesehatan()
                            ->exists();
                })
                ->url(function (): string {
                    return PemeriksaanKesehatanResource::getUrl('create', [
                        'pendaftaran_donor_id' => $this->record->id,
                    ]);
                }),

            Actions\Action::make('buat_kantong_darah')
                ->label('Buat Kantong Darah')
                ->icon('heroicon-o-beaker')
                ->color('success')
                ->visible(function (): bool {
                    return $this->record instanceof PendaftaranDonor
                        && $this->record->status === StatusPendaftaranDonor::Layak
                        && ! $this->record
                            ->kantongDarah()
                            ->exists();
                })
                ->url(function (): string {
                    return KantongDarahResource::getUrl('create', [
                        'pendaftaran_donor_id' => $this->record->id,
                    ]);
                }),
        ];
    }
}