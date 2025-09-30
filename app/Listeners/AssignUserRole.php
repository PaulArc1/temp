<?php

namespace App\Listeners;

use Spark\Events\SubscriptionCreated;

class AssignUserRole
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SubscriptionCreated $event): void
    {
    }
}
