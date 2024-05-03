<?php

namespace App\Listeners;

use Spatie\ModelStates\Events\StateChanged;

class LogOrderStatusChange
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
    public function handle(StateChanged $event): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($event->model)
            ->event('status-update')
            ->withProperties([
                'new'      => $event->finalState,
                'previous' => $event->initialState,
            ])->log('status-update');
    }
}
