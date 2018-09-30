<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
//use Illuminate\Foundation\Testing\DatabaseMigrations;


class ThreadsTest extends TestCase
{
    use RefreshDatabase;

    protected $thread;

    public function setUp()
    {
        parent::setUp();

        $this->thread = factory('App\Thread')->create();
    }

    /** @test */

    public function a_user_can_browse_threads()
    {

        $this->get('/threads')

            ->assertSee($this->thread->title);
    }

    /** @test */

    public function a_user_can_read_a_single_thread()
    {
        $this->get('/thread/' . $this->thread->channel . '/' . $this->thread->slug)
            ->assertSee($this->thread->title);
    }

    /** @test */

    public function a_user_can_read_replies_that_are_associated_with_a_thread()
    {
        $reply = factory('App\Reply')->create(['thread_id' => $this->thread->id]);

        $this->get('/thread/' .$this->thread->channel .'/'.  $this->thread->slug .'/replies')
            ->assertSee($reply->body);
    }

    /** @test */

    public function a_user_can_filter_threads_according_to_a_channel()
    {
       $channel = create('App\Channel');
       $threadInChannel = create('App\Thread', ['channel_id' => $channel->id]);
       $threadNotInChannel = create('App\Thread');

       $this->get('/thread/' . $channel->slug)
           ->assertSee($threadInChannel->title)
           ->assertDontSee($threadNotInChannel->title);
    }

    /** @test */

    public function a_user_can_filter_threads_by_any_username()
    {
        $this->signIn(create('App\User', ['name' => 'johnDoe']));

        $threadsByJohn = create('App\Thread', ['user_id' => auth()->id()]);
        $threadsNotByJohn = create('App\Thread');

        $this->get('threads?by=johnDoe')
            ->assertSee($threadsByJohn->title)
            ->assertDontSee($threadsNotByJohn->title);
    }

    /** @test */

    public function a_user_can_filter_threads_by_porpularity()
    {
        // Given we have 3 threads.
        // with, 2 replies, 3 replies, and 0 replies respectively.

        $threadsWithTwoReplies = create('App\Thread');
        create('App\Reply', ['thread_id' => $threadsWithTwoReplies->id], 2);

        $threadsWithThreeReplies = create('App\Thread');
        create('App\Reply', ['thread_id' => $threadsWithThreeReplies->id], 3);

        $threadWithNoReplies = $this->thread;

        // When i filter all threads by porpularity
        $response = $this->getJson('threads?popular=1')->json();

        //Then they should be returned from most replies to least
        $this->assertEquals([3,2,0], array_column($response['data'], 'replies_count'));

    }

    /** @test */

    public function a_user_can_filter_threads_by_those_that_are_unanswered()
    {
        $thread = create('App\Thread');
        create('App\Reply', ['thread_id' => $thread->id]);

        $response = $this->getJson('threads?unanswered=1')->json();

        $this->assertCount(1, $response['data']);
    }

    /** @test */

    public function a_user_can_request_all_replies_for_a_given_thread()
    {
        $thread = create('App\Thread');
        create('App\Reply', ['thread_id' => $thread->id], 2);

        $response = $this->getJson($thread->path() . '/replies')->json();

        $this->assertCount(2, $response['data']);
        $this->assertEquals(2, $response['total']);

    }

    /** @test */

    public function a_thread_can_be_subscribed_to()
    {
        // Given we have a thread
        $thread = $this->thread;

        // And an authenticated user
        //$this->signIn(); -- we didnt have to bother with signed-in user cos we directly inputed it ourselves

        //When the user subscribes to the thread
        $thread->subscribe($userId = 1);

        //Then we should be able to fetch all threads that the user has subscribed to
        $this->assertEquals(1, $thread->subscriptions()->where('user_id', $userId)->count());
    }

    /** @test */

    public function a_thread_can_be_unsubscribed_from()
    {
        // Given we have a thread
        //$thread = $this->thread;
        $thread = create('App\Thread');

        // And an authenticated user
        //$this->signIn(); -- we did'nt have to bother with this cos we directly inputed it ourselves

        //When the user subscribes to the thread
        $thread->subscribe($userId = 1);

        $thread->unsubscribe($userId);

        //Then we should be able to fetch all threads that the user has subscribed to
        //$this->assertCount(0, $thread->subscriptions());
        $this->assertEquals(0, $thread->subscriptions()->where('user_id', $userId)->count());
    }

    /** @test */ // Using the database method here instead of redis

    public function we_record_a_new_visit_each_time_a_thread_is_read()
    {
        $thread = create('App\Thread');

        $this->assertSame(0, $thread->visits);

        $this->call('GET', $thread->path());

        $this->assertEquals(1, $thread->fresh()->visits);
    }

    /** @test */

    public function a_threads_body_is_sanitized_automatically()
    {
        $thread = make('App\Thread', ['body' => '<script>alert("bad")</script><p>This is okay</p>']);

        $this->assertEquals("<p>This is okay</p>", $thread->body);
    }

}
