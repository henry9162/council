<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class LockThreadsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function non_administrators_may_not_lock_thread()
    {
        $this->withExceptionHandling(); //We ususlly do this to get the correct response

        $this->signIn();

        $thread = create('App\Thread', [
            'user_id' => auth()->id()
        ]);

        $this->post(route('locked-threads.store', $thread))->assertStatus(403);

        $this->assertFalse(!! $thread->fresh()->locked); // the double appostrophe means we casting it to a boolean
    }

    /** @test */

    public function an_administrators_can_lock_any_thread()
    {
        $this->signIn(factory('App\User')->states('administrator')->create());

        $thread = create('App\Thread', [
            'user_id' => auth()->id()
        ]);

        $this->post(route('locked-threads.store', $thread));

        $this->assertTrue(!! $thread->fresh()->locked, 'Failed asserting that the thread was locked.');

    }

    /** @test */

    public function an_administrators_can_unlock_any_thread()
    {
        $this->signIn(factory('App\User')->states('administrator')->create());

        $thread = create('App\Thread', [
            'user_id' => auth()->id(), 'locked' => true
        ]);

        $this->delete(route('locked-threads.destroy', $thread));

        $this->assertFalse(!! $thread->fresh()->locked, 'Failed asserting that the thread was locked.');

    }

    /** @test */

    public function once_locked_a_thread_may_not_receive_new_replies()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $thread->lock();

        $this->post($thread->path() . '/replies', [
            'body' => 'Foobar',
            'user_id' => create('App\User')->id
        ])->assertStatus(422);
    }

}


