<?php

namespace App\Http\Controllers;

use App\Activity;
use App\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        return view('profiles.show', [
            'profileUser' => $user, // not the authenticated user, but user of the profile
            'activities' => Activity::feed($user)
            //'activities' => $this->getActivity($user)
            //'threads' => $user->threads()->paginate(1)
        ]);
    }

    /**
     * @param User $user
     * @return mixed
     */
    /*protected function getActivity(User $user)
    {
        return $user->activity()->latest()->with('subject')->take(50)->get()->groupBy(function ($activity) {
            return $activity->created_at->format('y-m-d');
        });
    }*/
}
