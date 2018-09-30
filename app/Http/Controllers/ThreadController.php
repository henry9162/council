<?php

namespace App\Http\Controllers;

use App\Rules\Recaptcha;
use App\Thread;
use App\Trending;
use Carbon\Carbon;
use App\Channel;
use Illuminate\Http\Request;
use App\Filters\ThreadFilters;
use Illuminate\Support\Facades\Redis;
use Mockery\Exception;

class ThreadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }


    /*
     * public function index($channelSlug = null)
    {
        if ($channelSlug){
            $channelId = Channel::where('slug', $channelSlug)->first()->id;

            $threads = Thread::where('channel_id', $channelId)->latest()->get();
        } else {
            $threads = Thread::latest()->get();
        }

        return view('threads.index', compact('threads'));
    }*/


    /**
     * @param Channel $channel
     * @param ThreadFilters $filters
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */

    public function index(Channel $channel, ThreadFilters $filters, Trending $trending)
    {

         $threads = $this->getThreads($channel, $filters);

         if (request()->wantsJson()){
             return $threads;
         }

         /*$trending = collect(Redis::zrevrange('trending_threads', 0, 4))->map(function ($thread){
             return json_decode($thread);
         });*/

        //$trending = array_map('json_decode', Redis::zrevrange('trending_threads', 0, 4)); -- This was extracted to Trending class

        return view('threads.index', [
            'threads' => $threads,
            'trending' => $trending->get()
        ]);

        /* comment

        /*
         * The below goes with the getThreads method as a form of refactor
         * $threads = $this->getThreads($channel);
         *  return view('threads.index', compact('threads'));


        /*
         *  if ($channel->exists) {
            $threads = $channel->threads()->latest();
             } else {
                $threads = Thread::latest();
            }


        /*if request('by'), we should filter by the given username.

        if ($username = request('by')) {

            $user = \App\User::where('name', $username)->firstOrFail();

            $threads->where('user_id', $user->id);
        }

        */

    }

    public function create()
    {
        return view('threads.create');
    }

    /**
     * @param Request $request
     * @param Recaptcha $recaptcha
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request, Recaptcha $recaptcha)
    {
        $this->validate($request, [
            'title' => 'required|spamfree',
            'body'  => 'required|spamfree',
            'channel_id' => 'required',
            'g-recaptcha-response' => ['required', $recaptcha]
        ]);

        //$spam->detect(\request('body'));

        $thread = Thread::create([
            'user_id' => auth()->id(),
            'channel_id' => request('channel_id'),
            'title' => request('title'),
            'body' => request('body'),
            //'slug' => request('title') -- We dont need to set the slug explicitly here, the model event (boot method) takes care of that
        ]);

        if (request()->wantsJson()){
            return response($thread, 201);
        }

        return redirect($thread->path())
            ->with('flash', 'Your thread has been published!');
    }

    public function show($channelId, Thread $thread, Trending $trending)
    {
        // Record that the user visited the page

        //Record a timestamp

        if (auth()->check()){
            auth()->user()->read($thread);
        }

        /* This was moved/extracted to trending class
         *
         * Redis::zincrby('trending_threads', 1, json_encode([
            'title' => $thread->title,
            'path' => $thread->path()
        ]));
        */

        $trending->push($thread);

        /*We commented this out so we use database instead of redis
         *
         * $thread->visits()->record();*/

        $thread->increment('visits');

        /*$key = sprintf("users.%s.visits.%s", auth()->id(), $thread->id);

        cache()->forever($key, Carbon::now());*/

        //return $thread->load('replies.favorites')->load('replies.owner');
        //return view('threads.show', compact('thread'));

        return view('threads.show', compact('thread'));
    }

    public function update($channel, Thread $thread)
    {
        $this->authorize('update', $thread);

        // This way it returns the validated data i.e 'title' and 'body'.
        /*$data = request()->validate([
            'title' => 'required|spamfree',
            'body'  => 'required|spamfree'
        ]);

        $thread->update(request(['title', 'body']));*/

        //So we can use them like the below
        //$thread->update($data);  //Or further inline them like below

        $thread->update(request()->validate([
            'title' => 'required|spamfree',
            'body'  => 'required|spamfree'
        ]));

        return $thread; //or tap($thread)->update(request()->validate([]); as in just above
    }

    public function destroy($channel, Thread $thread)
    {
        //We made use of a ThreadPolicy for this, returns a 403 error if value not matched.
        $this->authorize('update', $thread);

        if ($thread->user_id != auth()->id()){
            /*if (request()->wantsJson()){
                return response(['status'=> 'Permission denied'], 403);
            }*/

            abort(403, 'You do not have permission to do this');
        }

        $thread->delete();

        if (request()->wantsJson()){
            return response([], 204);
        }

        return redirect('/threads');
    }

    /**
     * @param Channel $channel
     * @param ThreadFilters $filters
     * @return mixed
     */
    protected function getThreads(Channel $channel, ThreadFilters $filters)
    {
        $threads = Thread::latest()->filter($filters);

        if ($channel->exists) {
            $threads->where('channel_id', $channel->id);
        }

        //dd($threads->toSql());

        return $threads->paginate(5);

    }

    /*
     * The below method is a refactor class for the index method
     * protected function getThreads(Channel $channel)
    {
        if ($channel->exists) {
            $threads = $channel->threads()->latest();
        } else {
            $threads = Thread::latest();
        }

        // if request('by'), we should filter by the given username.

        if ($username = request('by')) {

            $user = \App\User::where('name', $username)->firstOrFail();

            $threads->where('user_id', $user->id);
        }

        $threads = $threads->get();

        return $threads;
    }*/

}
