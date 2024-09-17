<?php

namespace Tests\Unit\Services;

use App\Models\Player;
use App\Services\PlayerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerServiceTest extends TestCase
{
    use RefreshDatabase;

    private PlayerService $playerSerivce;

    protected function setUp(): void
    {
        parent::setUp();

        $this->playerSerivce = $this->app->make(PlayerService::class);
    }

    public function test_exists_email_ok()
    {
        $player = Player::factory()->create();

        $foundPlayer = $this->playerSerivce->existEmail($player->email);

        $this->assertInstanceOf(Player::class, $foundPlayer);
        $this->assertSame($player->email, $foundPlayer->email);
    }

    public function test_exists_email_failed()
    {
        $foundPlayer = $this->playerSerivce->existEmail('email@gmail.com');

        $this->assertNull($foundPlayer);
    }
}
