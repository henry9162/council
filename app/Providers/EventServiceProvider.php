<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ThreadReceivedNewReply' => [
            'App\Listeners\NotifyMentionedUsers',
            'App\Listeners\NotifySubscribers'
        ],

        /*We got read of this since its only one listener, added it directly to the register controller by laravel
         * Registered::class => [
            'App\Listeners\SendEmailConfirmationRequest'
        ]*/
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
