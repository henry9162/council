<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostRequest;
use App\Notifications\YouAreMentioned;
use App\Reply;
use App\Thread;
use App\User;
use Illuminate\Http\Request;


class ReplyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'index']);
    }

    public function index($channelId, Thread $thread)
    {
        return $thread->replies()->paginate(10);
    }

    public function store($channelId, Thread $thread, CreatePostRequest $form)
    {
        /* Wev dont need this here anymore, now refactored in CreatePostForm and our exception handler
         * if (Gate::denies('create', new Reply)){
            return response('You are posting too frequently. Please take a break. :)', 429);
        }*/

        if ($thread->locked){
            return response('Thread is locked', 422);
        }

        return $thread->addReply([
            'body' => request('body'),
            'user_id' => auth()->id()
        ])->load('creator');


        // return $form->persist($thread); // we can use this to refactor more

        /*
         * We dont need the try function to catch exception anymore since our form request is already taking care of that, check Exception to see my addition
         *
        try {
             //$this->authorize('create', new Reply);

              $this->validate(request(), ['body' => 'required|spamfree']);
             //$this->validateReply(); //we use custom validation rule (spam free) to take care of this instead of this method

             $reply = $thread->addReply([
               'body' => request('body'),
                'user_id' => auth()->id()
             ]);

        } catch (\Exception $e){
            return response('Sorry, your reply could not be saved at this time', 422);
        }
        */


        //return $reply->load('creator');

        /*if (request()->expectsJson()){
            return $reply->load('creator');
        }*/

    }

    public function destroy(Reply $reply)
    {
        /*if ($reply->user_id != auth()->id()){
            return response([], 403);
        }*/
        $this->authorize('update', $reply);

        $reply->delete();

        if (request()->expectsJson()){
            return response(['status' => 'Reply deleted']);
        }

        return back();
    }

    public function update(Reply $reply)
    {
        $this->authorize('update', $reply);

        /*try {
            $this->validate(request(), ['body' => 'required|spamfree']);
            //$this->validateReply(); we use custom validation rule to take care of this instead of the method

            $reply->update(request(['body']));
        } catch (\Exception $e){
            return response('Sorry, your reply could not be saved at this time', 422);
        }*/

        $this->validate(request(), ['body' => 'required|spamfree']);

        $reply->update(request(['body']));

        //$reply->update(['body' => request('body')]);
    }

    /*protected function validateReply()
    {
        $this->validate(request(), ['body' => 'required|spamfree']);

        resolve(Spam::class)->detect(\request('body'));
    }*/
}
