<?php
/**
 * Created by PhpStorm.
 * User: Ekwonwa Henry
 * Date: 30-Jun-18
 * Time: 9:10 AM
 */

namespace App;


trait favoritable
{

    //this means for any model using ths favoritable, if u delete the model then we want to make sure to delete its favorite also.
    protected static function bootFavoritable()
    {
        static::deleting(function ($model){
            $model->favorites->each(function ($favorite){
                $favorite->delete();
            });
        });
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorited');
    }

    public function favorite()
    {
        $attributes = ['user_id' => auth()->id()];

        if (!$this->favorites()->where($attributes)->exists()) {
            return $this->favorites()->create($attributes);
        }
    }

    public function isFavorited()
    {
        return !! $this->favorites->where('user_id', auth()->id())->count();
    }

    public function getIsFavoritedAttribute()
    {
        return $this->isFavorited();
    }

    public function getFavoritesCountAttribute()
    {
        return $this->favorites->count();
    }

    public function unfavorite()
    {
        $attributes = ['user_id' => auth()->id()];

        $this->favorites()->where($attributes)->get()->each->delete();

        /*$this->favorites()->where($attributes)->get()->each(function($favorite){
            $favorite->delete();
        });*/

        //if u want ur model event to pick up, u must delete the model instead of just a query builder.
    }

    /*public function isFavorited()
    {
         return !! $this->favorites->where('user_id', auth()->id())->count();
    }

    public function getIsFavoritedAttribute()
    {
       return $this->isFavorited();
    }*/

}