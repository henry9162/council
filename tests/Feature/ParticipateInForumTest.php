<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParticipateInForumTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function unauthenticated_users_may_not_add_replies()
    {
        //$this->expectException('Illuminate\Auth\AuthenticationException');
        $this->withExceptionHandling()
            ->post('/thread/some-channel/1/replies', [])
            ->assertRedirect('/login');
    }

    /** @test */

    public function an_authenticated_user_can_participate_in_forum_threads()
    {
        // Given we have an authenticated user
        $this->be($user = create('App\User'));

        //and an existing thread
        $thread  = create('App\Thread');

        //when the user adds reply to a thread
        $reply = make('App\Reply');
        $this->post($thread->path().'/replies', $reply->toArray());

        //then their reply should be visible on the page
        /*$this->get($thread->path() . '/replies')
             ->assertSee($reply->body);*/
        //OR

        $this->assertDatabaseHas('replies', ['body'=>$reply->body]);
        $this->assertEquals(1, $thread->fresh()->replies_count);
    }

    /** @test */

    public function unauthorized_users_cannot_delete_replies()
    {
        $this->withExceptionHandling();

        $reply = create('App\Reply');

        $this->delete("/replies/{$reply->id}")
            ->assertRedirect('login');

        $this->signIn()
            ->delete("/replies/{$reply->id}")
            ->assertStatus(403);
    }

    /** @test */

    public function authorized_users_can_delete_replies()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $this->delete("/replies/{$reply->id}");

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
        $this->assertEquals(0, $reply->thread->fresh()->replies_count);
    }

    /** @test */

    public function authorized_users_can_update_replies()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $updatedReply = 'You have been changed, fool.';
        $this->patch("/replies/{$reply->id}", ['body' => $updatedReply]);

        $this->assertDatabaseHas('replies', ['id' => $reply->id, 'body' => $updatedReply]);

    }

    /** @test */

    public function unauthorized_users_cannot_update_replies()
    {
        $this->withExceptionHandling();

        $reply = create('App\Reply');

        $this->patch("/replies/{$reply->id}")
            ->assertRedirect('login');

        $this->signIn()
            ->patch("/replies/{$reply->id}")
            ->assertStatus(403);
    }

    /** @test */

    public function reply_that_contains_spam_may_not_be_created()
    {
        $this->withExceptionHandling(); //we turn-on exception handling so that we can see the actual exception

        $this->signIn();

        $thread = create('App\Thread');

        $reply = make('App\Reply', [
            'body' => 'Yahoo Customer Support'
        ]);

        //$this->expectException(\Exception::class); -- we already are catching the exception in the controller
        $this->json('post',$thread->path() . '/replies', $reply->toArray())
            ->assertStatus(422);
    }

    /** @test */

    public function users_may_only_reply_a_maximum_of_once_per_minute()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $reply = make('App\Reply', [
            'body' => 'my simple reply'
        ]);

        $this->expectException(\Exception::class);

        $this->post($thread->path() . '/replies' . $reply->toArray())
            ->assertStatus(200);

        $this->post($thread->path() . '/replies' . $reply->toArray())
            ->assertStatus(422);
    }
}
