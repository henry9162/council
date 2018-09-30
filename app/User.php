<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'avatar_path'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'confirmed' => 'boolean'
    ];

    public function getRouteKeyName()
    {
        return 'name';
    }

    public function threads()
    {
        return $this->hasMany(Thread::class)->latest();
    }

    public function lastReply()
    {
        return $this->hasOne(Reply::class)->latest();
    }

    public function activity()
    {
        return $this->hasMany(Activity::class);
    }

    public function confirm()
    {
        $this->confirmed = true;

        $this->confirmation_token = null;

        $this->save();
    }

    public function isAdmin()
    {
        return in_array($this->name, ['Henry', 'Deborah']);
    }

    public function read($thread)
    {
        cache()->forever($this->visitedThreadCacheKey($thread), \Carbon\Carbon::now());
    }

    public function getAvatarPathAttribute($avatar) //Overwrite what get returned when u say $user->avatar_path
    {
        return asset($avatar ?: 'images/avatars/default.jpg');
        /*if (! $this->avatar_path){
            return 'avatars/default.jpg';
        }

        return $this->avatar_path;*/
    }

    public function visitedThreadCacheKey($thread)
    {
        return sprintf("users.%s.visits.%s", $this->id, $thread->id);
    }
}