<?php

namespace App;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class AppProvider extends EventServiceProvider
{
    /**
     * Map domain events to their listeners.
     *
     * DomainEvents that declare channels(), webhook(), or mail() are routed
     * automatically by Innertia — no listener needed for those.
     *
     * Example:
     *   use App\Domains\Orders\Events\OrderCreated;
     *   use App\Domains\Orders\Listeners\OnOrderCreated;
     *
     *   OrderCreated::class => [
     *       OnOrderCreated::class,
     *   ],
     */
    protected $listen = [];

    public function register(): void {}

    public function boot(): void
    {
        // El link de reset apunta al frontend SPA, no al backend Laravel.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $base  = config('app.frontend_url', config('app.url'));
            $email = urlencode($notifiable->getEmailForPasswordReset());

            return "{$base}/reset-password?token={$token}&email={$email}";
        });
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
