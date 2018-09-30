<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscribeToThreadsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function a_user_can_subscribe_to_threads()
    {
        $this->signIn();

        //Given we have a thread
        $thread = create('App\Thread');

        //And the user subscribe to the thread..
        $this->post($thread->path() . '/subscriptions');

        $this->assertCount(1, $thread->fresh()->subscriptions);
    }

    /** @test */

    public function a_user_can_unsubscribe_from_threads()
    {
        $this->signIn();

        //Given we have a thread
        $thread = create('App\Thread');

        //Subscribe the user to a thread..
        $thread->subscribe();

        $this->delete($thread->path() . '/subscriptions');

        $this->assertCount(0, $thread->subscriptions);
    }

    /** @test */

    public function it_knows_if_the_authenticated_user_is_subscribed_to_it()
    {
        //Given we have a thread
        $thread = create('App\Thread');

        $this->signIn();

        $this->assertFalse($thread->isSubscribedTo);

        $thread->subscribe();

        $this->assertTrue($thread->isSubscribedTo);
    }

}
