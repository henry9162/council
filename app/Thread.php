<?php

namespace App;

use App\Events\ThreadHasNewReply;
use App\Events\ThreadReceivedNewReply;
use App\Notifications\ThreadWasUpdated;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Purify;

class Thread extends Model
{
    use RecordsActivity, Searchable;

    protected $guarded =[];

    protected $with = ['creator', 'channel'];

    protected $appends = ['isSubscribedTo'];

    protected $casts = ['locked' => 'boolean']; // We want locked to always be a boolean


    protected static function boot()
    {
        parent::boot();

        //Had to comment this, because we are adding a replies_count column to our database
       /* static::addGlobalScope('replyCount', function ($builder) {
            $builder->withCount('replies');
        });*/

        static::deleting(function ($thread){
            $thread->replies->each->delete();
        });

        /*$thread->replies->each(function ($reply){
                $reply->delete();
            });*/

        /*static::addGlobalScope('creator', function ($builder) {
            $builder->with('creator');
        });*/

        static::created(function($thread){
            $thread->update(['slug' => $thread->title]);
        });
    }

    public function path()
    {
        return "/thread/{$this->channel->slug}/{$this->slug}";
        //return '/thread/' . $this->channel->slug .'/'. $this->id;
    }


    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }


    /**
     * Prepare notifications for all subscribers using the filter approach
     * ->each(function ($sub) use ($reply) {$sub->user->notify(new ThreadWasUpdated($this, $reply));})
     *
     * We use the filter approach instead of this
     * foreach ($this->subscriptions as $subscription){
        if ($subscription->user_id != $reply->user_id){
            $subscription->user->notify(new ThreadWasUpdated($this, $reply));
        }
     *
     * We use ->where('user_id', '!=', $reply->user_id ) instead of the below
     * ->filter(function($sub) use ($reply) { return $sub->user_id != $reply->user_id; })
     *
     * We used an event listener instead of this
     * $this->subscriptions
            ->where('user_id', '!=', $reply->user_id )
            ->each->notify($reply);
     */
    public function addReply($reply)
    {
        //(new \App\Spam)->detect($reply->body);

        $reply = $this->replies()->create($reply);

        event(new ThreadReceivedNewReply($reply));

        //$this->notifySubscribers($reply); // This has been taken care of as a listener (Notify subscribers)

        //event(new ThreadHasNewReply($this, $reply));

        return $reply;
    }

    /*
     * This has been taken care of in the listener (NotifySubscribers)
     public function notifySubscribers($reply)
    {
        $this->subscriptions
            ->where('user_id', '!=', $reply->user_id )
            ->each->notify($reply);
    }*/

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    public function subscribe($userId = null)
    {
        $this->subscriptions()->create([
            'user_id' => $userId ?: auth()->id()
        ]);

        return $this;
    }

    public function unsubscribe($userId = null)
    {
        $this->subscriptions()->where('user_id', $userId ?: auth()->id())->delete();
    }

    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    public function getIsSubscribedToAttribute()
    {
        return $this->subscriptions()->where('user_id', auth()->id())->exists();
    }

    public function hasUpdateFor($user)
    {
        //Look in the cache for the proper key

        //Compare that carbon instance with the $thread->updated_at

        //$user = $user ?: auth()->user();

        $key = $user->visitedThreadCacheKey($this);

        //remember to fix why using $user->visitedThreadCacheKey($this) wont work

        //$key = sprintf("users.%s.visits.%s", auth()->id(), $this->id);

        return $this->updated_at > cache($key);
    }

    /* We commenting this, so we use database instead of redis
     * public function visits()
    {
        //return Redis::get($this->visitCacheKey()) ?? 0;

        return new Visits($this);
    }*/

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function setSlugAttribute($value) // This will be triggered immediately when we set a value to the slug
    {
        $slug = str_slug($value);

        if (static::whereslug($slug)->exists()){
            $slug = "{$slug}-" . $this->id;
        }

        /*if (static::whereSlug($slug)->exists()){
            $slug = $this->incrementSlug($slug);
        }*/

        $this->attributes['slug'] = $slug;
    }

    /*public function incrementSlug($slug)
    {
        $max = static::whereTitle($this->title)->latest('id')->value('slug');

        if (is_numeric($max[-1])){
            return preg_replace_callback('/(\d+)$/', function($matches){
                return $matches[1] + 1;
            }, $max);
        }

        return "{$slug}-2";
    }*/

    public function MarkBestReply(Reply $reply)
    {
        $reply->thread->update(['best_reply_id' => $reply->id]);
    }

    public function lock()
    {
        $this->update(['locked' => true]);
    }

    public function toSearchableArray()
    {
        return $this->toArray() + ['path' => $this->path()];
    }

    public function getBodyAttribute($body)
    {
        return Purify::clean($body);
    }

}
