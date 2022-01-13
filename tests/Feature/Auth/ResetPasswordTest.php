<?php

namespace Tests\Feature\Auth;

use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_ok()
    {
        $player = Player::factory()->create();

        Notification::fake();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => $player->email,
        ]);

        $response->assertOk();
        Notification::assertSentTo($player, ResetPassword::class);
    }

    public function test_forgot_password_with_wrong_email()
    {
        $player = Player::factory()->create();

        Notification::fake();

        // Unavailable email
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'email@gmail.com',
        ]);

        $response->assertNotFound();
        Notification::assertNotSentTo($player, ResetPassword::class);

        // Invalid email
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'email@',
        ]);

        $response->assertStatus(400);
    }

    public function test_forgot_password_too_fast()
    {
        $player = Player::factory()->create();

        $this->postJson('/api/auth/forgot-password', [
            'email' => $player->email,
        ]);

        Notification::fake();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => $player->email,
        ]);

        $response->assertUnprocessable();
        Notification::assertNotSentTo($player, ResetPassword::class);
    }

    public function test_reset_password_ok()
    {
        $player = Player::factory()->create();
        $token = hash_hmac('sha256', Str::random(40), config('app.key'));

        DB::table('password_resets')->insert([
            'email' => $player->email,
            'token' => Hash::make($token),
            'created_at' => new Carbon(),
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $player->email,
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
            'token' => $token,
        ]);

        $response->assertOk();
        $this->assertNull(DB::table('password_resets')->where('email', $player->email)->first());
        $this->assertTrue(Hash::check('new_password', Player::find($player->id)->password));
    }

    public function test_reset_password_with_wrong_token()
    {
        $player = Player::factory()->create();
        $token = hash_hmac('sha256', Str::random(40), config('app.key'));

        DB::table('password_resets')->insert([
            'email' => $player->email,
            'token' => Hash::make($token),
            'created_at' => new Carbon(),
        ]);

        // Invalid token
        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $player->email,
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
            'token' => 'fake_token',
        ]);

        $response->assertForbidden();
        $this->assertFalse(Hash::check('new_password', Player::find($player->id)->password));

        // Ignore token
        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $player->email,
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ]);

        $response->assertStatus(400);
    }

    public function test_reset_password_with_wrong_email()
    {
        // Unavailable email
        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'email@gmail.com',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
            'token' => 'token',
        ]);

        $response->assertStatus(500);

        // Invalid email
        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'email@',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
            'token' => 'token',
        ]);

        $response->assertStatus(400);
    }

    public function test_reset_password_with_wrong_password()
    {
        // Too short
        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'email@gmail.com',
            'password' => 'n',
            'password_confirmation' => 'new_password',
            'token' => 'token',
        ]);

        $response->assertStatus(400);

        // Do not match
        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'email@gmail.com',
            'password' => 'new_password',
            'password_confirmation' => 'new_passwordd',
            'token' => 'token',
        ]);

        $response->assertStatus(400);
    }
}
