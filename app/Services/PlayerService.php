<?php

namespace App\Services;

use App\Models\Player;

class PlayerService
{
    /**
     * Get player by email if exist.
     *
     * @param string $email
     * @return \App\Models\Player|null
     */
    public function existEmail(string $email)
    {
        return Player::where('email', $email)->first();
    }
}
