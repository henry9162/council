<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

class RegisterConfirmationController extends Controller
{
    public function index()
    {
        //dd(request()->all());

        /*  This is one method to achieve this
         *
         *  $user = User::where('confirmation_token', request('token'))->firstOrFail();

            $user->confirmed = true;

            $user->save();
        */

        /*We used try catch here, becos firstOrFail will yield an exception, also only first() will produce null at some point which will break the application
         * try {
            User::where('confirmation_token', request('token'))->firstOrFail()->confirm();
        } catch (\Exception $e){
            return \redirect(route('threads'))->with+('flash', 'Unknown token.');
        }*/

        $user = User::where('confirmation_token', request('token'))->first();

        if (! $user){
            return redirect(route('threads'))->with('flash', 'Unknown token.');
        }

        $user->confirm();

        return redirect(route('threads'))->with('flash', 'Your account is now confirmed! You may post to the forum.');
    }
}
