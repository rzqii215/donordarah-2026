<?php

namespace App\Providers;

use App\Policies\ActivityPolicy;
use Filament\Actions\MountableAction;
use Filament\Notifications\Livewire\Notifications;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureLocalMailRecipient();

        Gate::policy(
            Activity::class,
            ActivityPolicy::class
        );

        Page::formActionsAlignment(
            Alignment::Right
        );

        Notifications::alignment(
            Alignment::End
        );

        Notifications::verticalAlignment(
            VerticalAlignment::End
        );

        Page::$reportValidationErrorUsing = function (
            ValidationException $exception
        ): void {
            Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();
        };

        MountableAction::configureUsing(
            function (MountableAction $action): void {
                $action->modalFooterActionsAlignment(
                    Alignment::Right
                );
            }
        );
    }

    private function configureLocalMailRecipient(): void
    {
        if (! $this->app->environment('local')) {
            return;
        }

        if (
            ! (bool) config(
                'mail.testing_recipient.enabled',
                false
            )
        ) {
            return;
        }

        $address = trim(
            (string) config(
                'mail.testing_recipient.address',
                ''
            )
        );

        if ($address === '') {
            return;
        }

        /*
         * Jangan memasukkan display name pada penerima.
         */
        Mail::alwaysTo($address);
    }
}