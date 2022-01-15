<?php

namespace Tests\Unit\app\Services;

use App\Models\Player;
use App\Services\ProfileService;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProfileService $profileSerivce;

    protected function setUp(): void
    {
        parent::setUp();

        $this->profileSerivce = $this->app->make(ProfileService::class);
    }

    public function test_update_username()
    {
        $player = Player::factory()->create();

        $status = $this->profileSerivce->updateUsername($player, 'new_name');

        $this->assertTrue($status);
        $this->assertSame('new_name', $player->username);
    }

    public function test_update_email()
    {
        $player = Player::factory()->create();

        Notification::fake();

        $status = $this->profileSerivce->updateEmail($player, 'email@gmail.com');

        $this->assertTrue($status);
        $this->assertNull($player->email_verified_at);

        Notification::assertSentTo($player, VerifyEmail::class);
    }

    public function test_update_password()
    {
        $player = Player::factory()->create();

        $status = $this->profileSerivce->updatePassword($player, 'new_password');

        $this->assertTrue($status);
        $this->assertTrue(Hash::check('new_password', $player->password));
    }
}
