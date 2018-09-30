<?php

namespace App\Http\Controllers;

use App\Thread;
use Illuminate\Http\Request;

class ThreadSubscriptionController extends Controller
{
    public function store($channel, Thread $thread)
    {
        $thread->subscribe();
    }

    public function destroy($channelId, Thread $thread)
    {
        $thread->unsubscribe();
    }
}
