<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MentionUsersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function mentioned_users_in_a_reply_are_notified()
    {
        // Give we have a user, Henry, who is signed in.

        $john = create('App\User', [
            'name' => 'Henry'
        ]);

        $this->signIn($john);

        // And another user Deborah

        $Deborah = create('App\User', [
            'name' => 'Deborah'
        ]);

        // If we have a thread

        $thread = create('App\Thread');

        // And Henry replies and mentions Deborah

        $reply = make('App\Reply', [
            'body' => '@Deborah look at this, also @Henry'
        ]);

        $this->json('post',$thread->path() . '/replies', $reply->toArray());

        // Then Deborah should be notified

        $this->assertCount(1, $Deborah->notifications);
    }

    /** @test */

    public function it_can_get_all_mentioned_users_starting_with_the_given_characters()
    {
        create('App\User', ['name' => 'johndoe']);
        create('App\User', ['name' => 'johndoe2']);
        create('App\User', ['name' => 'janedoe']);

        $results = $this->json('GET', '/api/users', ['name' => 'john']);

        $this->assertCount(2, $results->json());
    }
}
