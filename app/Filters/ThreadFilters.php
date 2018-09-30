<?php

namespace App\Filters;

use App\User;
use Illuminate\Http\Request;

class ThreadFilters extends Filters
{
    protected $filters = ['by', 'popular', 'unanswered'];

    /**
     * Filter the query by a given username
     * @param $builder
     * @param $username
     * @return mixed
     */

    protected function by($username)
    {
        $user = User::where('name', $username)->firstOrFail();

        return $this->builder->where('user_id', $user->id);
    }

    protected function popular()
    {
        //This clears out all existing orderby's already preset elsewhere and set this one by the replies_count.
        $this->builder->getQuery()->orders = [];

        return $this->builder->orderBy('replies_count', 'desc');
    }

    protected function unanswered()
    {
        return $this->builder->where('replies_count', 0);
    }
}
