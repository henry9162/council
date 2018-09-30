<?php
/**
 * Created by PhpStorm.
 * User: Ekwonwa Henry
 * Date: 28-Jun-18
 * Time: 4:26 AM
 */

namespace App\Filters;


use Illuminate\Http\Request;

abstract class Filters
{
    protected $request;
    protected $builder;

    protected $filters = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply($builder)
    {
        $this->builder = $builder;

        foreach ($this->getFilters() as $filter => $value) {
            if (method_exists($this, $filter)){
                $this->$filter($value);
            }
        }

        return $this->builder;

        /*if ($this->request->has('by')) {
            $this->by($this->request->by);
        }*/

        /**  if (! $username = $this->request->by) return $builder;
         *  return $this->by($builder, $username);
         *  since $this->builder = $builder;, it is assumed that $builder is now available everywhwere the method
         */
    }

    public function getFilters()
    {
        return $this->request->only($this->filters);
    }


}