<?php

namespace Tests\Feature;

use App\Activity;
use App\Rules\Recaptcha;
use App\Thread;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateThreadsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        // Mock the Recapture class
        // Assert that a passes method should be called
        // Then it should return true
        // Finally, take that mock and bind it within the container (singleton) to the recapture class.

        app()->singleton(Recaptcha::class, function(){
            $m = \Mockery::mock(Recaptcha::class);

            $m->shouldReceive('passes')->andReturn(true);

            return $m;
        });
    }

    /** @test */

    public function guest_may_not_create_threads()
    {
        $this->withExceptionHandling()
            ->get('/threads/create')
            ->assertRedirect(route('login'));

            $this->post(route('threads'))
            ->assertRedirect(route('login'));
    }

    /** @test */

    public function new_users_must_first_confirm_there_email_address_before_creating_threads()
    {
        $user = \factory('App\User')->states('unconfirmed')->create();

        $this->signIn($user);

        $thread = make('App\Thread');

        return $this->post('/threads', $thread->toArray())
            ->assertRedirect(route('threads'))
            ->assertSessionHas('flash');
    }

    /** @test */

    public function an_user_can_create_new_forum_threads()
    {
        //Given we have a signed in user
        //$this->actingAs(create('App\User'));
        $this->signIn();

        //When we hit the end point to create a new thread
        $thread = make('App\Thread');
        $response = $this->post(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token']);

        //dd($response->headers->get('Location'));

        //Then we visit the thread page
        $this->get($response->headers->get('Location'))
             ->assertSee($thread->title)
             ->assertSee($thread->body);
    }

    /** @test */

    public function a_thread_requires_a_title()
    {
        $this->publishThread(['title' => null])
            ->assertSessionHasErrors();
    }

    /** @test */

    /*public function a_thread_requires_recaptcha_verification()
    {
        unset(app()[Recaptcha::class]); // This unbinds the recaptcha class

        $this->publishThread(['g-recaptcha-response' => 'test'])
            ->assertSessionHasErrors();
    }*/

    /** @test */

    public function a_thread_requires_a_unique_slug()
    {
        $this->signIn();

        $thread = create('App\Thread', ['title' => 'Foo Title']);

        $this->assertEquals($thread->fresh()->slug, 'foo-title');

        $thread = $this->postJson(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token'])->json(); //Thread becomes an array bcos of json

        $this->assertEquals("foo-title-{$thread['id']}", $thread['slug']);

    }

    /** @test */

    public function a_thread_with_a_title_that_ends_in_a_number_should_generate_the_proper_slug()
    {
        $this->signIn();

        $thread = create('App\Thread', ['title' => 'Some Title 24']);

        $thread = $this->postJson(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token'])->json();

        $this->assertEquals("some-title-24-{$thread['id']}", $thread['slug']);
    }

    /** @test */

    public function unauthorized_users_may_not_delete_threads()
    {
        $this->withExceptionHandling();

        $thread = create('App\Thread');

        $this->delete($thread->path())->assertRedirect('/login');

        //Or use the json method, but then u have to redirect to 401 or 404 whatever code
        //$response = $this->json('DELETE', $thread->path());

        $this->signIn();

        $this->delete($thread->path())->assertStatus(403);
    }

    /** @test */

    public function authorized_users_can_may_deleted_thread()
    {
        $this->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id()]);
        $reply = create('App\Reply', ['thread_id' => $thread->id]);

        $response = $this->json('DELETE', $thread->path());

        $response->assertStatus(204);

        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);

        $this->assertEquals(0, Activity::count());
        //test for deleting the activity for both thread and reply
        /*$this->assertDatabaseMissing('activities', [
            'subject_id' => $thread->id,
            'subject_type' => get_class($thread)
        ]);

        $this->assertDatabaseMissing('activities', [
            'subject_id' => $reply->id,
            'subject_type' => get_class($thread)
        ]);*/
    }


    /**
     * @param array $overrides
     */
    public function publishThread($overrides = [])
    {
        $this->withExceptionHandling()->signIn();

        $thread = make('App\Thread', $overrides);

        return $this->post(route('threads'), $thread->toArray());
    }
}
