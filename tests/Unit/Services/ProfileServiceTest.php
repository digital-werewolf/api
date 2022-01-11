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

        $this->profileSerivce = new ProfileService();
    }

    public function test_update_username()
    {
        $player = Player::factory()->unverified()->create();

        $rlt = $this->profileSerivce->updateUsername($player, 'new_name');

        $this->assertTrue($rlt);
        $this->assertSame('new_name', $player->username);
    }

    public function test_update_email()
    {
        $player = Player::factory()->create();

        Notification::fake();

        $rlt = $this->profileSerivce->updateEmail($player, 'email@gmail.com');

        $this->assertTrue($rlt);
        $this->assertNull($player->email_verified_at);

        Notification::assertSentTo($player, VerifyEmail::class);
    }

    public function test_update_password()
    {
        $player = Player::factory()->create();

        $rlt = $this->profileSerivce->updatePassword($player, 'new_password');

        $this->assertTrue($rlt);
        $this->assertTrue(Hash::check('new_password', $player->password));
    }
}
