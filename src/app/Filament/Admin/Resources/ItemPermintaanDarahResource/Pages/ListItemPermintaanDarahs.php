<?php

namespace App\Filament\Admin\Resources\ItemPermintaanDarahResource\Pages;

use App\Enums\StatusPermintaanDarah;
use App\Filament\Admin\Resources\ItemPermintaanDarahResource;
use App\Models\PermintaanDarah;
use App\Services\LayananAlokasiDarah;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListItemPermintaanDarahs extends ListRecords
{
    protected static string $resource =
        ItemPermintaanDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make(
                'alokasi_otomatis'
            )
                ->label('Alokasi Otomatis')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->form([
                    Forms\Components\Select::make(
                        'permintaan_darah_id'
                    )
                        ->label('Permintaan Darah')
                        ->options(
                            fn (): array => PermintaanDarah::query()
                                ->whereIn(
                                    'status',
                                    [
                                        StatusPermintaanDarah
                                            ::Disetujui
                                            ->value,

                                        StatusPermintaanDarah
                                            ::MenungguStok
                                            ->value,

                                        StatusPermintaanDarah
                                            ::SiapDiambil
                                            ->value,
                                    ]
                                )
                                ->with('rumahSakit')
                                ->orderByDesc(
                                    'created_at'
                                )
                                ->get()
                                ->filter(
                                    fn (
                                        PermintaanDarah $record
                                    ): bool => $record
                                        ->sisaKebutuhanKantong() > 0
                                )
                                ->mapWithKeys(
                                    fn (
                                        PermintaanDarah $record
                                    ): array => [
                                        $record->id => sprintf(
                                            '%s — %s — %s%s — Sisa %d',
                                            $record
                                                ->nomor_permintaan,
                                            $record
                                                ->rumahSakit
                                                ->nama_rumah_sakit,
                                            $record
                                                ->golongan_darah
                                                ->label(),
                                            $record
                                                ->rhesus
                                                ->simbol(),
                                            $record
                                                ->sisaKebutuhanKantong(),
                                        ),
                                    ]
                                )
                                ->all()
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->modalHeading(
                    'Alokasi Kantong Darah Otomatis'
                )
                ->modalDescription(
                    'Sistem memilih kantong dengan masa kedaluwarsa terdekat terlebih dahulu.'
                )
                ->action(
                    function (
                        array $data
                    ): void {
                        $permintaan =
                            PermintaanDarah::query()
                                ->findOrFail(
                                    $data[
                                        'permintaan_darah_id'
                                    ]
                                );

                        $hasil = app(
                            LayananAlokasiDarah::class
                        )->alokasikanOtomatis(
                            permintaan: $permintaan,
                            petugasId: (int) Filament
                                ::auth()
                                ->id(),
                        );

                        if ($hasil->isEmpty()) {
                            Notification::make()
                                ->title(
                                    'Belum ada stok sesuai yang dapat dialokasikan.'
                                )
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title(
                                $hasil->count()
                                . ' kantong darah berhasil dialokasikan.'
                            )
                            ->success()
                            ->send();
                    }
                ),

            Actions\CreateAction::make()
                ->label('Alokasi Manual')
                ->icon('heroicon-o-plus'),
        ];
    }
}