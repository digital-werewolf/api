<?php

namespace Tests\Unit\Services;

use App\Models\Lock;
use App\Models\LockedAction;
use App\Models\Player;
use App\Services\LockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LockServiceTest extends TestCase
{
    use RefreshDatabase;

    private LockService $lockSerivce;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lockSerivce = $this->app->make(LockService::class);
    }

    public function test_is_locked_without_lock()
    {
        $player = Player::factory()->create();

        $lockReason = $this->lockSerivce->isLocked($player, 'sign-in');

        $this->assertNull($lockReason);
    }

    public function test_is_locked_with_lock()
    {
        $player = Player::factory()->create();
        $lockedAction = LockedAction::factory()->create([
            'name' => Str::random(10),
            'message' => Str::random(10),
        ]);
        $lock = Lock::factory()->create([
            'player_id' => $player->id,
            'action_id' => $lockedAction->id,
            'reason' => Str::random(10),
            'expired_at' => now('GMT')->addMinutes(5),
        ]);

        $lockReason = $this->lockSerivce->isLocked($player, $lockedAction->name);

        $this->assertIsString($lockReason);
        $this->assertStringContainsString($lockedAction->message, $lockReason);
        $this->assertStringContainsString($lock->reason, $lockReason);
    }

    public function test_is_locked_with_expired_time()
    {
        $player = Player::factory()->create();
        $lockedAction = LockedAction::factory()->create([
            'name' => Str::random(10),
            'message' => Str::random(10),
        ]);
        Lock::factory()->create([
            'player_id' => $player->id,
            'action_id' => $lockedAction->id,
            'reason' => Str::random(10),
            'expired_at' => now('GMT')->subMinutes(5),
        ]);

        $lockReason = $this->lockSerivce->isLocked($player, $lockedAction->name);

        $this->assertNull($lockReason);
    }

    public function test_calculate_remaining_time_is_greater_than_now()
    {
        $player = Player::factory()->create();
        $lockedAction = LockedAction::factory()->create([
            'name' => Str::random(10),
            'message' => Str::random(10),
        ]);
        $lock = Lock::factory()->create([
            'player_id' => $player->id,
            'action_id' => $lockedAction->id,
            'reason' => Str::random(10),
            'expired_at' => now('GMT')->addMinutes(5),
        ]);

        $diff = $this->lockSerivce->calculateRemainingTime($lock);

        $this->assertSame(4, $diff);
    }

    public function test_calculate_remaining_time_is_smaller_than_now()
    {
        $player = Player::factory()->create();
        $lockedAction = LockedAction::factory()->create([
            'name' => Str::random(10),
            'message' => Str::random(10),
        ]);
        $lock = Lock::factory()->create([
            'player_id' => $player->id,
            'action_id' => $lockedAction->id,
            'reason' => Str::random(10),
            'expired_at' => now('GMT')->subMinutes(5),
        ]);

        $diff = $this->lockSerivce->calculateRemainingTime($lock);

        $this->assertSame(-5, $diff);
    }
}
