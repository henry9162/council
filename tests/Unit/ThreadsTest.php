<?php

namespace Tests\Unit;

use App\Notifications\ThreadWasUpdated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    public function a_thread_has_a_path()
    {
        //create() persist it, creating an id, rather than make()
        $thread = create('App\Thread');

        $this->assertEquals("/thread/{$thread->channel->slug}/{$thread->slug}", $thread->path());
        //$this->assertEquals('/thread/'. $thread->channel->slug .'/'. $thread->id, $thread->path());
    }

    /** @test */

    public function a_thread_has_a_creator()
    {
        $this->assertInstanceOf('App\User', $this->thread->creator);
    }

    /** @test */

    public function a_thread_has_replies()
    {
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->thread->replies);
    }

    /** @test */

    public function a_thread_can_add_a_reply()
    {
        $this->thread->addReply([
            'body' => 'foobar',
            'user_id' => 1
        ]);

        $this->assertCount(1, $this->thread->replies);
    }

    /** @test */

    public function a_thread_notifies_all_registered_subscribers_when_a_reply_is_added()
    {
        Notification::fake();

        $this->signIn();

        $this->signIn()->thread->subscribe()->addReply([
            'body' => 'foobar',
            'user_id' => 999
        ]);

        Notification::assertSentTo(auth()->user(), ThreadWasUpdated::class);
    }

    /** @test */

    public function a_thread_belongs_to_a_channel()
    {
        $thread = create('App\Thread');

        $this->assertInstanceOf('App\Channel', $thread->channel);
    }

    /** @test */

    public function a_thread_can_check_if_the_authenticated_user_has_read_all_replies()
    {
        $this->signIn();

        $thread = create('App\Thread');

        tap(auth()->user(), function($user) use ($thread){
            //new thread that has'nt been visited, since every thread wips a reply to it
            $this->assertTrue($thread->hasUpdateFor($user));

            //simulate that the user visited the thread
            $user->read($thread);

            $this->assertFalse($thread->hasUpdateFor($user));
        });
    }

    /** @test */

    /*We commented this to test for the database approach instead of redis
     *
     * public function a_thread_records_each_visit()
    {
        $thread = make('App\Thread', ['id' => 1]);

        //Redis::get("threads.{$thread->id}.visits");

        //$thread->resetVisits();

        $thread->visits()->reset();

        $this->assertSame(0, $thread->visits()->count());

        $thread->visits()->record();

        $this->assertEquals(1, $thread->visits()->count());

        $thread->visits()->record();

        $this->assertEquals(2, $thread->visits()->count());
    }*/

    /** @test */

    public function a_thread_can_be_locked()
    {
        self::assertFalse($this->thread->locked);

        $this->thread->lock();

        $this->assertTrue($this->thread->locked);
    }
}
