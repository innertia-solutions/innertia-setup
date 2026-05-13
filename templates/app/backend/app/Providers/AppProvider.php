<?php

namespace App\Providers;

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

    public function boot(): void {}

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
