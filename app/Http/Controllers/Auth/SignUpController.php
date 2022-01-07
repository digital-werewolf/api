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
     * @param  \App\Http\Requests\Auth\SignUpRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signUp(SignUpRequest $request)
    {
        $validated = $request->validated();

        $validated['password'] = bcrypt($validated['password']);

        $player = Player::create($validated);

        $token = $player->createToken('sign-in')->plainTextToken;

        return response()->json([
            'data' => $player,
            'token' => $token,
        ], 201);
    }
}
