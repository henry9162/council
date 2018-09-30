<?php

namespace Tests\Feature;

use App\Mail\PleaseConfirmYourEmail;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function a_confirmation_email_is_sent_upon_registration()
    {
        Mail::fake();

        //event(new Registered(create('App\User'))); // We assumed a registered user here //also we commented everything since we no more using an event listener

        $this->post('/register', [
            'name' => 'john',
            'email' => 'john@exaample.com',
            'password' => 'foobar',
            'password_confirmation' => 'foobar'
        ]);

        Mail::assertSent(PleaseConfirmYourEmail::class);
    }

    /** @test */

    public function user_can_fully_confirm_there_email_addresses()
    {
        Mail::fake();

        $this->post(route('register'), [
            'name' => 'john',
            'email' => 'john@exaample.com',
            'password' => 'foobar',
            'password_confirmation' => 'foobar'
        ]);

        $user = User::whereName('john')->first();

        $this->assertFalse($user->confirmed); // We set confirm to always be a boolean on the model class using cast for this to work

        $this->assertNotNull($user->confirmation_token);

        // Let the User confirm their account.
        //$response = $this->get('/register/confirm?token=' . $user->confirmation_token);
        $this->get(route('register.confirm', ['token' => $user->confirmation_token] ))
            ->assertRedirect(route('threads'));

        tap($user->fresh(), function($user){
            $this->assertTrue($user->confirmed);
            $this->assertNull($user->confirmation_token);
        });
    }

    /** @test */

    public function confirming_an_invalid_token()
    {
        $this->get(route('register.confirm', ['token' => 'invalid'] ))
            ->assertRedirect(route('threads'))
            ->assertSessionHas('flash');
    }


}
