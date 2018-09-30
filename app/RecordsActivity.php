<?php
/**
 * Created by PhpStorm.
 * User: Ekwonwa Henry
 * Date: 02-Jul-18
 * Time: 2:36 AM
 */

namespace App;


use Tests\Feature\ActivityTest;

trait RecordsActivity
{
    protected static function bootRecordsActivity()
    {
        if (auth()->guest()) return;

        foreach (static::getActivitiesToRecord() as $event){

            static::$event(function($model) use ($event){
                $model->recordActivity($event);
            });
        }

        static::deleting(function($model){
            $model->activity()->delete();
        });

        /*static::created(function ($thread){
            $thread->recordActivity('created');
        });*/
    }

    protected static function getActivitiesToRecord()
    {
        return ['created'];
    }

    protected function recordActivity($event)
    {

        $this->activity()->create([
            'user_id' => auth()->id(), //'this' represent $thread every where here in this method, because its in the instance of static::created
            'type' => $this->getActivityType($event)
        ]);

        /*Activity::create([
            'user_id' => $this->creator->id,
            'subject_id' => $this->id,
            'subject_type' => get_class($this),
            'type' => $this->getActivityType($event)
        ]);*/
    }

    public function activity()
    {
        return $this->morphMany('App\Activity', 'subject');
    }

    protected function getActivityType($event)
    {
        $type = strtolower((new \ReflectionClass($this))->getShortName());

        return "{$event}_{$type}";

        //return $event . '_' . $type;
    }
}