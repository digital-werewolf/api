<?php

namespace Tests\Feature\Auth;

use App\Models\Lock;
use App\Models\LockedAction;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SignInTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_in_ok()
    {
        $player = Player::factory()->create();

        // With username
        $response = $this->postJson('/api/auth/sign-in', [
            'usernameOrEmail' => $player->username,
            'password' => 'password',
        ]);

        $response
            ->assertCreated()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('token')->etc()
            );

        // With email
        $response = $this->postJson('/api/auth/sign-in', [
            'usernameOrEmail' => $player->email,
            'password' => 'password',
        ]);

        $response
            ->assertCreated()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('token')->etc()
            );
    }

    public function test_sign_in_with_wrong_username()
    {
        // Incorrect
        $response = $this->postJson('/api/auth/sign-in', [
            'usernameOrEmail' => 'username',
            'password' => 'passwordd',
        ]);

        $response->assertStatus(400);
    }

    public function test_sign_in_with_wrong_email()
    {
        // Invalid email
        $response = $this->postJson('/api/auth/sign-in', [
            'usernameOrEmail' => 'email@',
            'password' => 'passwordd',
        ]);

        $response->assertStatus(400);

        // Incorrect
        $response = $this->postJson('/api/auth/sign-in', [
            'usernameOrEmail' => 'email@gmail.com',
            'password' => 'passwordd',
        ]);

        $response->assertStatus(400);
    }

    public function test_sign_in_with_wrong_password()
    {
        $player = Player::factory()->create();

        // Too short
        $response = $this->postJson('/api/auth/sign-in', [
            'usernameOrEmail' => $player->username,
            'password' => 'p',
        ]);

        $response->assertStatus(400);

        // Incorrect
        $response = $this->postJson('/api/auth/sign-in', [
            'usernameOrEmail' => $player->username,
            'password' => 'passwordd',
        ]);

        $response->assertStatus(400);
    }

    public function test_sign_in_with_locked_player()
    {
        $player = Player::factory()->create();
        $lockedAction = LockedAction::factory()->create([
            'name' => 'sign-in',
        ]);
        Lock::factory()->create([
            'player_id' => $player->id,
            'action_id' => $lockedAction->id,
            'expired_at' => now('GMT')->addMinutes(5),
        ]);

        $response = $this->postJson('/api/auth/sign-in', [
            'usernameOrEmail' => $player->username,
            'password' => 'password',
        ]);

        $response->assertForbidden();
    }
}
