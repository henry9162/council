<?php

namespace App\Policies;

use App\Reply;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReplyPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * Immediately u add a policy, make sure u go to appSeviceProvoider to register it
     *
     * @return void
     */
    /*public function __construct()
    {
        //
    }*/

    public function update(User $user, Reply $reply)
    {
        return $reply->user_id == $user->id;
    }

    public function create(User $user)
    {
        //The apostrophe makes it mean it wasn't just published
        $lastReply = $user->fresh()->lastReply;

        if (! $lastReply ) return true;

        return ! $lastReply->wasJustPublished();
    }
}
