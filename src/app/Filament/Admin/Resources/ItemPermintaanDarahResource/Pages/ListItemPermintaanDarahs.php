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
    protected static string $resource = ItemPermintaanDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('alokasi_otomatis')
                ->label('Alokasi Otomatis')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('permintaan_darah_id')
                        ->label('Pengajuan Kebutuhan Donor')
                        ->options(
                            fn (): array => PermintaanDarah::query()
                                ->whereIn(
                                    'status',
                                    [
                                        StatusPermintaanDarah::Disetujui->value,
                                        StatusPermintaanDarah::MenungguStok->value,
                                        StatusPermintaanDarah::SiapDiambil->value,
                                    ]
                                )
                                ->with([
                                    'rumahSakit',
                                ])
                                ->orderByDesc('created_at')
                                ->get()
                                ->filter(
                                    fn (PermintaanDarah $record): bool => $record
                                        ->sisaKebutuhanKantong() > 0
                                )
                                ->mapWithKeys(
                                    fn (PermintaanDarah $record): array => [
                                        $record->id => sprintf(
                                            '%s — %s — %s%s — Sisa %d',
                                            $record->nomor_permintaan,
                                            $record->rumahSakit?->nama_rumah_sakit ?? 'Pemohon Donor Tidak Ditemukan',
                                            $record->golongan_darah->label(),
                                            $record->rhesus->simbol(),
                                            $record->sisaKebutuhanKantong(),
                                        ),
                                    ]
                                )
                                ->all()
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->modalHeading('Alokasi Kantong Darah Otomatis')
                ->modalDescription('Sistem akan memilih kantong darah yang sesuai dengan golongan darah, rhesus, status mutu, status stok, dan masa kedaluwarsa terdekat.')
                ->action(function (array $data): void {
                    $permintaan = PermintaanDarah::query()
                        ->findOrFail($data['permintaan_darah_id']);

                    $hasil = app(LayananAlokasiDarah::class)
                        ->alokasikanOtomatis(
                            permintaan: $permintaan,
                            petugasId: (int) Filament::auth()->id(),
                        );

                    if ($hasil->isEmpty()) {
                        Notification::make()
                            ->title('Belum ada kantong darah yang sesuai untuk dialokasikan.')
                            ->body('Periksa kembali stok darah, status mutu kantong, golongan darah, rhesus, dan masa kedaluwarsa.')
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title($hasil->count() . ' kantong darah berhasil dialokasikan.')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Alokasi Manual')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'Alokasi Kantong Darah';
    }

    public function getHeading(): string
    {
        return 'Alokasi Kantong Darah';
    }

    public function getSubheading(): ?string
    {
        return 'Kelola alokasi kantong darah untuk pengajuan kebutuhan donor yang sudah disetujui atau sedang menunggu stok.';
    }
}