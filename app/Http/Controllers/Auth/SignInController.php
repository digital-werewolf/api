<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignInRequest;
use App\Models\Player;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SignInController extends Controller
{
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
