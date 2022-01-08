<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\SignInRequest;
use App\Http\Requests\Auth\SignUpRequest;
use App\Models\Player;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthController extends Controller
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

    /**
     * Sign in to system.
     *
     * @param  \App\Http\Requests\Auth\SignInRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signIn(SignInRequest $request)
    {
        $validated = $request->validated();

        $player = Player::where('username', $validated['usernameOrEmail'])
            ->orWhere('username', $validated['usernameOrEmail'])
            ->first();

        $correctPassword = $player === null
            ? false
            : Hash::check($validated['password'], $player->password);

        if ($correctPassword === false) {
            throw new BadRequestHttpException('Username or password is incorrect.');
        }

        return response()->json([
            'token' => $player->createToken('sign-in')->plainTextToken,
        ]);
    }
}
