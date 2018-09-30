<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class AddAvatarTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function only_members_can_add_avatars()
    {
        $this->withExceptionHandling(); //Remember, its so we can catch & get correct feedback of the exception handled

        $this->json('POST', 'api/users/1/avatar')
            ->assertStatus(401);
    }

    /** @test */

    public function a_valid_avatar_must_be_provided()
    {
        $this->withExceptionHandling()->signIn();

        $this->json('POST', 'api/users/' . auth()->id() . '/avatar', [
            'avatar' => 'not_an_image'
        ])->assertStatus(422); //422 means unprocessesable entity
    }

    /** @test */

    public function a_user_may_add_an_avatar_to_their_profile()
    {
        $this->signIn();

        Storage::fake('public');

        $this->json('POST', 'api/users/' . auth()->id() . '/avatar', [
            'avatar' => $file = UploadedFile::fake()->image('avatar.jpg')
        ]);

        $this->assertEquals(asset('avatars/' . $file->hashName()), auth()->user()->avatar_path);

        Storage::disk('public')->assertExists('avatars/' . $file->hashName());
    }

}
