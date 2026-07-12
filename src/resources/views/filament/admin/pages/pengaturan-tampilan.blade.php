<x-filament-panels::page>
    <form wire:submit="simpan">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button
                type="submit"
                icon="heroicon-m-check"
                wire:loading.attr="disabled"
                wire:target="simpan"
            >
                <span wire:loading.remove wire:target="simpan">
                    Simpan Pengaturan
                </span>

                <span wire:loading wire:target="simpan">
                    Menyimpan...
                </span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>