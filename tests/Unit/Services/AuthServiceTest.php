<?php

namespace Tests\Unit\app\Services;

use App\Models\Player;
use App\Services\AuthService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class AuthServiceTest extends TestCase
{
    private array $data;

    private Player $mockPlayer;

    private AuthService $authSerivce;

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'player' => [
                'username' => 'username',
                'email' => 'email@email.com',
                'password' => 'password',
            ],
            'credentials' => [
                'usernameOrEmail' => 'player02',
                'password' => 'password',
            ],
            'hashMakeOptions' => [ 'rounds' => 10 ],
            'hashedPassword' => 'hashed_password',
        ];

        // Hash::shouldReceive('make')
        //     ->with($this->data['player']['password'], $this->data['hashMakeOptions'])
        //     ->andReturn($this->data['hashedPassword']);

        $this->authSerivce = new AuthService();
    }

    // public function test_create_player_successfully()
    // {
    //     $this->mockPlayer = Mockery::mock('overload:' . Player::class);
    //     $this->mockPlayer
    //         ->shouldReceive('save')
    //         ->andReturn(true);

    //     $result = $this->authSerivce->createPlayer($this->data['player']);
    //     $this->assertInstanceOf(Player::class, $result);
    // }

    // public function test_create_player_failed()
    // {
    //     $this->mockPlayer = Mockery::mock('overload:' . Player::class);
    //     $this->mockPlayer
    //         ->shouldReceive('save')
    //         ->andReturn(false);

    //     $this->expectException(HttpException::class);
    //     $this->authSerivce->createPlayer($this->data['player']);
    // }

    // public function test_create_oauth_player_successfully()
    // {
    //     $this->mockPlayer = Mockery::mock('overload:' . Player::class);
    //     $this->mockPlayer
    //         ->shouldReceive('markEmailAsVerified')
    //         ->andReturn(true);

    //     $result = $this->authSerivce->createOAuthPlayer($this->data['player']['email']);
    //     $this->assertInstanceOf(Player::class, $result);
    // }

    // public function test_create_oauth_player_failed()
    // {
    //     $this->mockPlayer = Mockery::mock('overload:' . Player::class);
    //     $this->mockPlayer
    //         ->shouldReceive('markEmailAsVerified')
    //         ->andReturn(false);

    //     $this->expectException(HttpException::class);
    //     $this->authSerivce->createOAuthPlayer($this->data['player']['email']);
    // }

    // public function test_create_personal_access_token()
    // {
    //     $this->mockPlayer = Mockery::mock(Player::class);
    //     $this->mockPlayer
    //         ->shouldReceive('createToken')
    //         ->with('sign-in')
    //         ->andReturn(new NewAccessToken(new PersonalAccessToken(), 'token_string'));

    //     $result = $this->authSerivce->createPAT($this->mockPlayer);
    //     $this->assertIsString($result);
    // }

    // public function test_revoke_current_personal_access_token()
    // {
    //     /** @var \Laravel\Sanctum\PersonalAccessToken $mockPAT */
    //     $mockPAT = Mockery::mock(PersonalAccessToken::class);
    //     $mockPAT->shouldReceive('delete')
    //         ->andReturn(true, false);

    //     $this->mockPlayer = Mockery::mock(Player::class);
    //     $this->mockPlayer
    //         ->shouldReceive('currentAccessToken')
    //         ->andReturn($mockPAT);

    //     $result = $this->authSerivce->revokePAT($this->mockPlayer);
    //     $this->assertTrue($result);

    //     $this->expectException(HttpException::class);
    //     $this->authSerivce->revokePAT($this->mockPlayer);
    // }

    // public function test_revole_all_personal_access_tokens()
    // {
    //     /** @var \Illuminate\Database\Eloquent\Relations\MorphMany $mockPATs */
    //     $mockPATs = Mockery::mock(MorphMany::class);
    //     $mockPATs->shouldReceive('delete')
    //         ->andReturn(true, false);

    //     $this->mockPlayer = Mockery::mock(Player::class);
    //     $this->mockPlayer
    //         ->shouldReceive('tokens')
    //         ->andReturn($mockPATs);

    //     $result = $this->authSerivce->revokeAllPATs($this->mockPlayer);
    //     $this->assertTrue($result);

    //     $this->expectException(HttpException::class);
    //     $this->authSerivce->revokeAllPATs($this->mockPlayer);
    // }

    public function test_authenticate()
    {
        // /**
        //  * @var \App\Models\Player $mockPlayer
        //  */
        // $mockPlayer = Mockery::mock(Player::class);
        // $mockPlayer
        //     ->disableOriginalConstructor()
        //     ->shouldReceive('password')
        //         ->andReturn($this->data['player']['password'])
        //     ->shouldReceive('first')
        //         ->andReturn(111111);

        $mockPlayer = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor();

        // $this->app->instance(Player::class, $this->mockPlayer);
        // $this->app->bind(Player::class, function() use ($mockPlayer){
        //     return $mockPlayer;
        // });

        // $this->authSerivce = $this->app->make(AuthService::class);

        // Hash::shouldReceive('check')
        //     ->with($this->data['credentials']['password'], $this->data['player']['password'])
        //     ->andReturn(true, false);

        $result = $this->authSerivce->authenticate($this->data['credentials']);
        $this->assertSame(22222, $result);

        // $this->expectException(BadRequestHttpException::class);
        // $this->assertSame($this->mockPlayer, $result);

        // $this->expectException(BadRequestHttpException::class);
        // $this->assertSame($this->mockPlayer, $result);
    }
}
