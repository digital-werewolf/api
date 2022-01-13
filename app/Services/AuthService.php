<?php

namespace App\Services;

use App\Models\BlackPlayer;
use App\Models\Player;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthService
{
    /**
     * Create a player.
     *
     * @param array<string, string> $player
     * @return \App\Models\Player
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function createPlayer($player)
    {
        $player['password'] = Hash::make($player['password'], ['rounds' => 10]);

        $player = new Player($player);

        if (!$player->save()) {
            throw new HttpException(500, 'Unable to create account');
        }

        return $player;
    }

    /**
     * Create a oauth player.
     *
     * @param string $email
     * @return \App\Models\Player
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function createOAuthPlayer(string $email)
    {
        $username = substr(md5($email), 0, 10);

        $player = new Player([
            'username' => $username,
            'email' => $email,
        ]);

        // Save player with verified email
        if (!$player->markEmailAsVerified()) {
            throw new HttpException(500, 'Unable to create account');
        }

        return  $player;
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
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function revokePAT(Player $player)
    {
        /**
         * @var \App\Models $PAT
         */
        $PAT = $player->currentAccessToken();

        if (!$PAT->delete()) {
            throw new HttpException(500, 'Unable to revoke token');
        }

        return true;
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
            ->orWhere('email', $credentials['usernameOrEmail'])
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
            $this->unlockPlayer($player);

            return null;
        }

        return 'Reason: ' . $lock->reason
            . '. Automatically unlock after ' . round($remainingTime / 60) . ' hour(s).';
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

        return $now->diffInMinutes($expiredAt, false);
    }

    /**
     * Unlock the player.
     *
     * @param \App\Models\Player $player
     * @return bool
     */
    public function unlockPlayer(Player $player)
    {
        $lock = $player->lock;

        if (is_null($lock)) {
            return false;
        }

        return $player->lock->delete();
    }

    /**
     * Get player if exist.
     *
     * @param string $email
     * @return \App\Models\Player|null
     */
    public function existEmail(string $email)
    {
        return Player::where('email', $email)->first();
    }
}
