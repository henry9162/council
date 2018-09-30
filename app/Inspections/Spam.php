<?php

namespace App\Inspections;


class Spam
{
    protected $inspections = [
        InvalidKeyWords::class,
        KeyHeldDown::class
    ];

    public function detect($body)
    {
        foreach ($this->inspections as $inspection){
            app($inspection)->detect($body);
        }

        //if exception was not thrown
        return false;
    }

}