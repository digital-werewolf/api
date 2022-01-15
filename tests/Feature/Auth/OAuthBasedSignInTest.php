<?php

namespace Tests\Feature\Auth;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User;
use Mockery;
use Tests\TestCase;

class OAuthBasedSignInTest extends TestCase
{
    use RefreshDatabase;

    private $supportedDriver = [
        'github',
        'facebook',
        'google',
    ];

    public function test_redirect_ok()
    {
        foreach ($this->supportedDriver as $driver) {
            $response = $this->getJson('/api/auth/' . $driver . '/redirect');

            $response->assertRedirect();
        }
    }

    public function test_redirect_not_found()
    {
        $response = $this->getJson('/api/auth/fake/redirect');

        $response->assertNotFound();
    }

    public function test_callback_ok()
    {
        /**
         * @var \Laravel\Socialite\Two\User|mixed $oauthUser
         */
        $oauthUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $oauthUser->shouldReceive('getEmail')->andReturn('email@gmail.com');

        /**
         * @var \Laravel\Socialite\Contracts\Provider|mixed $provider
         */
        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->andReturn($oauthUser);

        foreach ($this->supportedDriver as $driver) {
            Socialite::shouldReceive('driver')->with($driver)->andReturn($provider);

            $response = $this->getJson('/api/auth/' . $driver . '/callback');

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) =>
                    $json->has('token')->etc()
                );

            $this->assertNotNull(Player::where('email', 'email@gmail.com')->first());
        }
    }

    public function test_callback_not_found()
    {
        $response = $this->getJson('/api/auth/fake/callback');

        $response->assertNotFound();
    }
}
