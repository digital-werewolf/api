<?php

namespace App\Services;

use App\Models\BlackPlayer;
use App\Models\Player;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthService
{
    /**
     * Create a player.
     *
     * @param array<string, string> $player
     * @return \App\Models\Player
     */
    public function createPlayer($player)
    {
        $player['password'] = Hash::make($player['password'], ['rounds' => 10]);

        $player = Player::create($player);

        return $player;
    }

    /**
     * Create a Personal Access Token.
     *
     * @param \App\Models\Player $player
     * @return string
     */
    public function createPAT(Player $player)
    {
        return $player->createToken('sign-in')->plainTextToken;
    }

    /**
     * Revoke current Personal Access Tokens of the player.
     *
     * @param \App\Models\Player $player
     * @return bool
     */
    public function revokePAT(Player $player)
    {
        return (Model::class)($player->currentAccessToken())->delete();
    }

    /**
     * Revoke all Personal Access Tokens of the player.
     *
     * @param \App\Models\Player $player
     * @return bool
     */
    public function revokeAllPATs(Player $player)
    {
        return $player->tokens()->delete();
    }

    /**
     * Authenticate username and password.
     *
     * @param array<string, string> $credentials
     * @return \App\Models\Player
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function authenticate($credentials)
    {
        $player = Player::where('username', $credentials['usernameOrEmail'])
            ->orWhere('username', $credentials['usernameOrEmail'])
            ->first();

        $isCorrectPassword = is_null($player)
            ? false
            : Hash::check($credentials['password'], $player->password);

        if (!$isCorrectPassword) {
            throw new BadRequestHttpException('Username or password is incorrect.');
        }

        return $player;
    }

    /**
     * Check if the player is locked.
     *
     * @param \App\Models\Player $player.
     * @return string|null
     */
    public function checkMoral(Player $player)
    {
        $lock = $player->lock;

        if (is_null($lock)) {
            return null;
        }

        $remainingTime = $this->getLockTime($lock);

        if ($remainingTime <= 0) {
            $this->unlockPlayer($lock);

            return null;
        }

        return 'Reason: ' . $lock->reason
            . '. Automatically unlock after ' . $remainingTime . ' hour(s).';
    }

    /**
     * Get remaining lock time of the player in minutes.
     *
     * @param \App\Models\BlackPlayer $blackPlayer
     * @return int
     */
    public function getLockTime(BlackPlayer $blackPlayer)
    {
        $now =  Carbon::now(new DateTimeZone('GMT'));
        $expiredAt = Carbon::parse($blackPlayer->expired_at);

        return $now->diffInMinutes($expiredAt);
    }

    /**
     * Unlock the player.
     *
     * @param \App\Models\BlackPlayer $blackPlayer
     * @return bool
     */
    public function unlockPlayer(BlackPlayer $blackPlayer)
    {
        return $blackPlayer->delete();
    }
}
