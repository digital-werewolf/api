<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignUpRequest;
use App\Models\Player;

class SignUpController extends Controller
{
    /**
     * Create an account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signUp(SignUpRequest $request)
    {
        $validated = $request->validated();

        $validated['password'] = bcrypt($validated['password']);

        $player = Player::create($validated);

        $token = $player->createToken('mm')->plainTextToken;

        return response()->json([
            'data' => $player,
            'token' => $token,
        ], 201);
    }
}
