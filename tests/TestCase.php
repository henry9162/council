<?php

namespace Tests;

use App\Exceptions\Handler;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Symfony\Component\Debug\ExceptionHandler;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp()
    {
        parent::setUp();

        //automatically disables exception handling by calling this class below
        $this->disableExceptionHanding();
    }


    protected function signIn($user = null)
    {
        $user = $user ?: create('App\User');

        $this->actingAs($user);

        return $this;
    }

    protected function disableExceptionHanding()
    {
        $this->oldExceptionHandler = $this->app->make(ExceptionHandler::class);

        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            //when an exception is thrown, it will delegate to this class
            public function __construct(){}
            public function report(\Exception $e) {}
            public function render($request, \Exception $e){
                throw $e;
            }
        });
    }

    public function withExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, $this->oldExceptionHandler);

        return $this;
    }
}
