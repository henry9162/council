<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReplyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function it_has_an_owner()
    {
        $reply = factory('App\Reply')->create();

        $this->assertInstanceOf('App\User', $reply->creator);
    }

    /** @test */

    public function it_knows_if_it_was_just_published()
    {
        $reply = create('App\Reply');

        $this->assertTrue($reply->wasJustPublished());

        $reply->created_at = Carbon::now()->subMonth();

        $this->assertFalse($reply->wasJustPublished());
    }

    /** @test */

    public function it_can_detect_all_mentioned_users_in_the_body()
    {
        $reply = create('App\Reply', [
            'body' => '@Deborah wants to talk to @Henry'
        ]);

        $this->assertEquals(['Deborah', 'Henry'], $reply->mentionedUsers());
    }

    /** @test */

    public function it_wraps_mentioned_username_in_the_body_within_anchor_tags()
    {
        $reply = create('App\Reply', [
            'body' => 'Hello @Deborah.'
        ]);

        $this->assertEquals('Hello <a href="/profiles/Deborah">@Deborah</a>.', $reply->body);
    }

    /** @test */

    public function it_knows_if_it_is_the_best_reply()
    {
        $reply = create('App\Reply');

        $this->assertFalse($reply->isBest());

        $reply->thread->update(['best_reply_id' => $reply->id]);

        $this->assertTrue($reply->fresh()->isBest());
    }

    /** @test */

    public function a_threads_body_is_sanitized_automatically()
    {
        $thread = make('App\Reply', ['body' => '<script>alert("bad")</script><p>This is okay</p>']);

        $this->assertEquals("<p>This is okay</p>", $thread->body);
    }
}
