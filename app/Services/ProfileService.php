<?php

namespace App\Services;

use App\Models\Player;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProfileService
{
    /**
     * Update player's username.
     *
     * @param \App\Models\Player $player
     * @param string $newUsername
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function updateUsername(Player $player, string $newUsername)
    {
        $player->username = $newUsername;

        if (!$player->save()) {
            throw new HttpException('Unable to update username!');
        }

        return true;
    }

    /**
     * Update player's email.
     *
     * @param \App\Models\Player $player
     * @param string $newEmail
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function updateEmail(Player $player, string $newEmail)
    {
        $player->email = $newEmail;
        $player->email_verified_at = null;

        if (!$player->save()) {
            throw new HttpException(500, 'Unable to update email!');
        }

        $player->sendEmailVerificationNotification();

        return true;
    }

    /**
     * Update player's email.
     *
     * @param \App\Models\Player $player
     * @param string $newEmail
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function updatePassword(Player $player, string $newPassword)
    {
        $hasedPassword = Hash::make($newPassword, ['rounds' => 10]);

        $player->password = $hasedPassword;

        if (!$player->save()) {
            throw new HttpException(500, 'Unable to update password!');
        }

        return true;
    }
}
