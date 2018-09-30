<?php

namespace App\Http\Controllers;

use App\Thread;
use Illuminate\Http\Request;

class LockedThreadController extends Controller
{
    public function store(Thread $thread)
    {
        if (! auth()->user()->isAdmin()){
            return response('You do not have permissions to lock this thread.', 403);
        }

        $thread->lock();
    }

    public function destroy(Thread $thread)
    {
        $thread->update(['locked' => false]);
    }
}
