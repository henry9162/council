<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
//use Illuminate\Foundation\Testing\DatabaseMigrations;


class FavoritesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function guest_cannot_favorite_anything()
    {
        $this->withExceptionHandling()
            ->post('replies/1/favorites')
            ->assertRedirect('/login');
    }

    /** @test */

    public function an_authenticated_user_can_favorite_any_reply()
    {
        $this->signIn();

        //replies/id/favorite
        $reply = create('App\Reply');

        //dd($reply->id);

        //If i post to a favorite endpoint
        $this->post('replies/' . $reply->id . '/favorites');

        //dd(\App\Favorite::all());

        $this->assertCount(1, $reply->favorites);
    }

    /** @test */

    public function an_authenticated_user_can_unfavorite_any_reply()
    {
        $this->signIn();

        $reply = create('App\Reply');

        $reply->favorite();

        $this->delete('replies/' . $reply->id . '/favorites');
        $this->assertCount(0, $reply->favorites);
    }

    /** @test */

    public function an_authenticated_user_can_favorite_a_reply_once()
    {

        $this->signIn();

        $reply = create('App\Reply');

        try{
            $this->post('replies/' . $reply->id . '/favorites');
            $this->post('replies/' . $reply->id . '/favorites');
        } catch (\Exception $e){
            $this->fail('Did not expect to insert the same record set twice.');
        }

        //dd(\App\Favorite::all()->toArray());

        $this->assertCount(1, $reply->favorites);
    }

}