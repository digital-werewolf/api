<?php

namespace Tests\Unit\app\Services;

use App\Models\BlackPlayer;
use App\Models\Player;
use App\Services\AuthService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authSerivce;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authSerivce = new AuthService();
    }

    public function test_create_player()
    {
        $player = $this->authSerivce->createPlayer([
            'username' => 'name',
            'email' => 'email@gmail.com',
            'password' => 'password',
        ]);

        $this->assertInstanceOf(Player::class, $player);
        $this->assertTrue(Hash::check('password', $player->password));
    }

    public function test_create_oauth_player_successfully()
    {
        $player = $this->authSerivce->createOAuthPlayer('email@gmail.com');

        $this->assertInstanceOf(Player::class, $player);
        $this->assertSame(10, strlen($player->username));
        $this->assertNull($player->password);
    }

    public function test_create_personal_access_token()
    {
        $player = Player::factory()->create();

        $token = $this->authSerivce->createPAT($player);

        $this->assertIsString($token);
        $this->assertStringContainsString("|", $token);
    }

    public function test_revoke_current_personal_access_token()
    {
        $player = Player::factory()->create();
        $token = $this->authSerivce->createPAT($player);
        $player->withAccessToken(PersonalAccessToken::findToken($token));

        $status = $this->authSerivce->revokePAT($player);

        $this->assertTrue($status);
    }

    public function test_revole_all_personal_access_tokens()
    {
        $player = Player::factory()->create();
        $this->authSerivce->createPAT($player);

        $status = $this->authSerivce->revokeAllPATs($player);

        $this->assertTrue($status);
    }

    public function test_authenticate_ok()
    {
        $player = Player::factory()->create();

        $authenticatedPlayer = $this->authSerivce->authenticate([
            'usernameOrEmail' => $player->email,
            'password' => 'password',
        ]);

        $this->assertInstanceOf(Player::class, $authenticatedPlayer);
        $this->assertSame($player->email, $authenticatedPlayer->email);

        $authenticatedPlayer = $this->authSerivce->authenticate([
            'usernameOrEmail' => $player->username,
            'password' => 'password',
        ]);

        $this->assertInstanceOf(Player::class, $authenticatedPlayer);
        $this->assertSame($player->username, $authenticatedPlayer->username);
    }

    public function test_authenticate_wrong_email()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->authSerivce->authenticate([
            'usernameOrEmail' => 'random@gmail.com',
            'password' => 'password',
        ]);
    }

    public function test_authenticate_wrong_password()
    {
        $player = Player::factory()->create();

        $this->expectException(BadRequestHttpException::class);
        $this->authSerivce->authenticate([
            'usernameOrEmail' => $player->username,
            'password' => 'passwordd',
        ]);
    }

    public function test_check_moral_has_not_locked_yet()
    {
        $player = Player::factory()->create();

        $lockReason = $this->authSerivce->checkMoral($player);

        $this->assertNull($lockReason);
    }

    public function test_check_moral_has_locked()
    {
        $player = Player::factory()->create();
        $lock = BlackPlayer::create([
            'player_id' => $player->id,
            'reason' => 'Lock reason!!!',
            'expired_at' => now('GMT')->addMinutes(5),
        ]);

        $lockReason = $this->authSerivce->checkMoral($player);

        $this->assertIsString($lockReason);
        $this->assertStringContainsString($lock->reason, $lockReason);
    }

    public function test_check_moral_time_expired()
    {
        $player = Player::factory()->create();
        BlackPlayer::create([
            'player_id' => $player->id,
            'reason' => 'Lock reason!!!',
            'expired_at' => now('GMT')->subMinutes(5),
        ]);

        $lockReason = $this->authSerivce->checkMoral($player);

        $this->assertNull($lockReason);
    }

    public function test_get_lock_time_greater_than_now()
    {
        $player = Player::factory()->create();
        $lock = BlackPlayer::create([
            'player_id' => $player->id,
            'reason' => 'Lock reason!!!',
            'expired_at' => now('GMT')->addMinutes(5),
        ]);

        $diff = $this->authSerivce->getLockTime($lock);

        $this->assertSame(4, $diff);
    }

    public function test_get_lock_time_smaller_than_now()
    {
        $player = Player::factory()->create();
        $lock = BlackPlayer::create([
            'player_id' => $player->id,
            'reason' => 'Lock reason!!!',
            'expired_at' => now('GMT')->subMinutes(5),
        ]);

        $diff = $this->authSerivce->getLockTime($lock);

        $this->assertSame(-5, $diff);
    }

    public function test_unlock_player_ok()
    {
        $player = Player::factory()->create();
        BlackPlayer::create([
            'player_id' => $player->id,
            'reason' => 'Lock reason!!!',
            'expired_at' => now('GMT'),
        ]);

        $status = $this->authSerivce->unlockPlayer($player);

        $this->assertTrue($status);
    }

    public function test_unlock_player_falied()
    {
        $player = Player::factory()->create();

        $status = $this->authSerivce->unlockPlayer($player);

        $this->assertFalse($status);
    }

    public function test_exists_email_ok()
    {
        $player = Player::factory()->create();

        $foundPlayer = $this->authSerivce->existEmail($player->email);

        $this->assertInstanceOf(Player::class, $foundPlayer);
        $this->assertSame($player->email, $foundPlayer->email);
    }

    public function test_exists_email_failed()
    {
        $foundPlayer = $this->authSerivce->existEmail('email@gmail.com');

        $this->assertNull($foundPlayer);
    }
}
