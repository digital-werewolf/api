<?php

namespace App\Services;

use App\Models\Lock;
use App\Models\Player;
use Carbon\Carbon;
use DateTimeZone;

class LockService
{
    /**
     * Check if the player is locked.
     *
     * @param \App\Models\Player $player
     * @param string $action
     * @return string|null
     */
    public function isLocked(Player $player, string $action)
    {
        $lock = Lock::where('player_id', $player->id)
            ->with('action')
            ->whereHas('action', function ($query) use ($action) {
                $query->where('name', $action);
            })
            ->first();

        if (is_null($lock)) {
            return null;
        }

        $remainingTime = $this->calculateRemainingTime($lock);

        // Unlock if expired time is exceeded
        if ($remainingTime <= 0) {
            $lock->delete();

            return null;
        }

        return $lock->action->message
            . ' Reason: ' . $lock->reason . '.'
            . ' Automatically unlock after ' . round($remainingTime / 60) . ' hour(s).';
    }

    /**
     * Get remaining lock time of the player in minutes.
     *
     * @param \App\Models\Lock $lock
     * @return int
     */
    public function calculateRemainingTime(Lock $lock)
    {
        $now =  Carbon::now(new DateTimeZone('GMT'));
        $expiredAt = Carbon::parse($lock->expired_at);

        return $now->diffInMinutes($expiredAt, false);
    }
}
