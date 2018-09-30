<?php

namespace App\Listeners;

use App\Events\ThreadReceivedNewReply;
use App\Notifications\YouAreMentioned;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyMentionedUsers
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ThreadReceivedNewReply  $event
     * @return void
     */
    public function handle(ThreadReceivedNewReply $event)
    {
        // Inspect the body of the reply for username mentioned
        //preg_match_all('/\@([^\s\.]+)/', $event->reply->body, $matches); //note that preg_match gives you only the first match, but preg_match_all gives u every match

        /*$mentionedUsers = $event->reply->mentionedUsers();

         And then, for each mentioned user, notify them
        foreach ($mentionedUsers as $name){
            $user = User::whereName($name)->first();


            if ($user){
                $user->notify(new YouAreMentioned($event->reply));
            }
        }*/

        /*
         * collect($event->reply->mentionedUsers())
            ->map(function($name){
                return User::whereName($name)->first();}) //the filter removes every null value/instances received.
            ->filter()->each(function($user) use ($event){
            $user->notify(new YouAreMentioned($event->reply));
        });
         * Instead of getting collection of users and then mapping them, and filtering for null values
         * We simply refactored to use the below
        */

        User::whereIn('name', $event->reply->mentionedUsers())->get()
                ->each(function($user) use ($event) {
                    $user->notify(new YouAreMentioned($event->reply));
                });
    }
}
