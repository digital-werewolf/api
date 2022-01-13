<?php

namespace Tests\Feature\Auth;

use App\Models\Player;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VerifyEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_verification_email_ok()
    {
        $player = Player::factory()->unverified()->create();
        $token = $player->createToken('sign-in')->plainTextToken;

        Notification::fake();

        $response = $this->postJson('/api/auth/send-verification-email', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        Notification::assertSentTo($player, VerifyEmail::class);
    }

    public function test_send_verification_email_to_verified_email()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;

        Notification::fake();

        $response = $this->postJson('/api/auth/send-verification-email', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        Notification::assertNotSentTo($player, VerifyEmail::class);
    }

    public function test_send_verification_email_wrong_token()
    {
        $response = $this->postJson('/api/auth/send-verification-email', [], [
            'Authorization' => 'Bearer ' . 'token',
        ]);

        $response->assertUnauthorized();
    }

    public function test_send_verification_email_without_token()
    {
        $response = $this->postJson('/api/auth/send-verification-email');

        $response->assertUnauthorized();
    }

    public function test_verify_email_ok()
    {
        $player = Player::factory()->unverified()->create();
        $token = $player->createToken('sign-in')->plainTextToken;
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $player->id, 'hash' => sha1($player->email)],
        );

        $response = $this->getJson($verificationUrl, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $this->assertTrue(Player::find($player->id)->hasVerifiedEmail());
    }

    public function test_verify_email_that_has_been_verified()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $player->id, 'hash' => sha1($player->email)],
        );

        $response = $this->getJson($verificationUrl, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
    }

    public function test_verify_email_with_wrong_id()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => 99, 'hash' => sha1($player->email)],
        );

        $response = $this->getJson($verificationUrl, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertForbidden();
    }

    public function test_verify_email_with_wrong_hash()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $player->id, 'hash' => sha1('email@gmail.com')],
        );

        $response = $this->getJson($verificationUrl, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertForbidden();
    }

    public function test_verify_email_without_signature()
    {
        $player = Player::factory()->create();
        $token = $player->createToken('sign-in')->plainTextToken;
        $verificationUrl = URL::route(
            'verification.verify',
            ['id' => $player->id, 'hash' => sha1($player->email)],
        );

        $response = $this->getJson($verificationUrl, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertForbidden();
    }

    public function test_verify_email_without_token()
    {
        $player = Player::factory()->create();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $player->id, 'hash' => sha1($player->email)],
        );

        $response = $this->getJson($verificationUrl);

        $response->assertUnauthorized();
    }
}
