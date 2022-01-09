<?php

namespace App\Services;

use App\Models\Player;
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
        $player['password'] = bcrypt($player['password']);

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
}
