<?php

namespace Tests\Feature\Auth;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SignUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_up_ok()
    {
        $response = $this->postJson('/api/auth/sign-up', [
            'username' => 'username',
            'email' => 'email@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $player = Player::where('username', 'username')->first();

        $this->assertNotNull($player);
        $response
            ->assertCreated()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('token')
                    ->where('data.username', 'username')
                    ->where('data.email', 'email@gmail.com')
                    ->etc()
            );
    }

    public function test_sign_up_with_wrong_username()
    {
        $player = Player::factory()->create();

        // Duplicate
        $response = $this->postJson('/api/auth/sign-up', [
            'username' => $player->username,
            'email' => 'email@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(400);

        // Too short
        $response = $this->postJson('/api/auth/sign-up', [
            'username' => 'u',
            'email' => 'email@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(400);

        // Too long
        $response = $this->postJson('/api/auth/sign-up', [
            'username' => 'usernameeeeeeeeeeeee',
            'email' => 'email@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(400);
    }

    public function test_sign_up_with_wrong_email()
    {
        $player = Player::factory()->create();

        // Duplicate
        $response = $this->postJson('/api/auth/sign-up', [
            'username' => 'username',
            'email' => $player->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(400);

        // Invalid email
        $response = $this->postJson('/api/auth/sign-up', [
            'username' => 'username',
            'email' => 'dasdsad@',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(400);
    }

    public function test_sign_up_with_wrong_password()
    {
        // Too short
        $response = $this->postJson('/api/auth/sign-up', [
            'username' => 'username',
            'email' => 'email@gmail.com',
            'password' => 'p',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(400);

         // Do not match
         $response = $this->postJson('/api/auth/sign-up', [
            'username' => 'username',
            'email' => 'email@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'passwordd',
        ]);

        $response->assertStatus(400);
    }
}
