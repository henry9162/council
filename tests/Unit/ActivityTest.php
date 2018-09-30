<?php

namespace Tests\Feature;

use App\Activity;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function it_records_activity_when_a_thread_is_created()
    {
        $this->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id()]);

        $this->assertDatabaseHas('activities',[
            'user_id' => auth()->id(),
            'subject_id' => $thread->id,
            'subject_type' => 'App\Thread',
            'type' => 'created_thread'
        ]);

        $activity = Activity::first();

        $this->assertEquals($activity->subject->id, $thread->id);
    }

    /** @test */

    public function it_records_activity_when_a_reply_is_created()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $this->assertEquals(2, Activity::count());
    }

    /** @test */

    public function it_fetches_a_activity_feed_for_any_user()
    {
        //Given we have a thread
        $this->signIn();

        create('App\Thread', ['user_id' => auth()->id()], 2);

        auth()->user()->activity()->first()->update(['created_at' => Carbon::now()->subWeek()]);

        //And another thread from a week ago
        /*create('App\Thread', [
            'user_id' => auth()->id(),
            'created_at' => Carbon::now()->subWeek()
        ]);*/

        //When we fetch their feed.
        $feed = Activity::feed(auth()->user(), 50);

        //Then it should be returned in the proper format
        $this->assertTrue($feed->keys()->contains(
            Carbon::now()->format('y-m-d')
        ));
    }
}
