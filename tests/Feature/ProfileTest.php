<?php

namespace Tests\Feature;

use App\Models\Player;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_profile_ok()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;

        $response = $this->getJson('api/profile', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data')
                    ->where('data.email', $player->email)
                    ->etc()
            );
    }

    public function test_get_profile_without_token()
    {
        $response = $this->getJson('api/profile');

        $response->assertUnauthorized();
    }

    public function test_update_username_ok()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;

        $response = $this->putJson('api/profile/username',
        [
            'username' => 'username'
        ],
        [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $this->assertSame('username', Player::find($player->id)->username);
    }

    public function test_update_username_with_wrong_username()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;

        // Too short
        $response = $this->putJson('api/profile/username',
        [
            'username' => 'u'
        ],
        [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(400);

        // Too long
        $response = $this->putJson('api/profile/username',
        [
            'username' => 'usernameeeeeeeee'
        ],
        [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(400);

        // Unavailable username
        $response = $this->putJson('api/profile/username',
        [
            'username' => $player->username,
        ],
        [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(400);
    }

    public function test_update_username_without_token()
    {
        $response = $this->putJson('api/profile/username', [
            'username' => 'username'
        ]);

        $response->assertUnauthorized();
    }

    public function test_update_email_ok()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;

        Notification::fake();

        $response = $this->putJson('api/profile/email',
        [
            'email' => 'email@gmail.com'
        ],
        [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        Notification::assertSentTo($player, VerifyEmail::class);
        $this->assertSame('email@gmail.com', Player::find($player->id)->email);
    }

    public function test_update_email_with_wrong_email()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;

        // Invalid email
        $response = $this->putJson('api/profile/email',
        [
            'email' => 'email@'
        ],
        [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(400);

        // Unavailable email
        $response = $this->putJson('api/profile/email',
        [
            'email' => $player->email,
        ],
        [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(400);
    }

    public function test_update_email_without_token()
    {
        $response = $this->putJson('api/profile/email', [
            'email' => 'email@gmail.com'
        ]);

        $response->assertUnauthorized();
    }

    public function test_update_password_ok()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;

        $response = $this->putJson('api/profile/password',
        [
            'password' => 'new_password',
            'password_confirmation' => 'new_password'
        ],
        [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('new_password', Player::find($player->id)->password));
    }

    public function test_update_password_with_wrong_password()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;

        // Too short
        $response = $this->putJson('api/profile/password',
        [
            'password' => 'n',
            'password_confirmation' => 'new_password'
        ],
        [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(400);

        // Do not match
        $response = $this->putJson('api/profile/password',
        [
            'password' => 'new_password',
            'password_confirmation' => 'new_passwordd'
        ],
        [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(400);
    }

    public function test_update_password_without_token()
    {
        $response = $this->putJson('api/profile/email', [
            'password' => 'new_password',
            'password_confirmation' => 'new_password'
        ]);

        $response->assertUnauthorized();
    }
}
