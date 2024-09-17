<?php

namespace Tests\Unit\app\Services;

use App\Models\Lock;
use App\Models\Player;
use App\Services\AuthService;
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

        $this->authSerivce = $this->app->make(AuthService::class);
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

        $this->assertGreaterThan(0, $status);
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
}
