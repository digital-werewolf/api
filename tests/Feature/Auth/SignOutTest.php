<?php

namespace Tests\Feature\Auth;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_out_ok()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;

        $response = $this->postJson('/api/auth/sign-out', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
    }

    public function test_sign_out_with_wrong_token()
    {
        $response = $this->postJson('/api/auth/sign-out', [], [
            'Authorization' => 'Bearer ' . 'token',
        ]);

        $response->assertUnauthorized();
    }

    public function test_sign_out_without_token()
    {
        $response = $this->postJson('/api/auth/sign-out');

        $response->assertUnauthorized();
    }
}
