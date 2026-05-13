<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Map domain events to their listeners.
     *
     * Listeners should be thin — they receive the event and delegate
     * to a UseCase. Example:
     *
     *   use App\Domains\Orders\Events\OrderCreated;
     *   use App\Domains\Orders\Listeners\OnOrderCreated;
     *
     *   OrderCreated::class => [
     *       OnOrderCreated::class,      // → (new ReserveInventory(...))->execute()
     *       SendOrderConfirmation::class,
     *   ],
     *
     * DomainEvents that declare channels() (realtime, webhook, mail) are
     * routed automatically by Innertia — no listener needed for those.
     */
    protected $listen = [
        //
    ];

    public function boot(): void {}

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
