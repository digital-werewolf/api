<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignInRequest;
use App\Http\Requests\Auth\SignUpRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthenticationController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;

        $this->middleware('auth:sanctum')->only(['signOut']);
    }

    /**
     * Create an account.
     *
     * @param  \App\Http\Requests\Auth\SignUpRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signUp(SignUpRequest $request)
    {
        $validated = $request->validated();

        $player = $this->authService->createPlayer($validated);
        $token = $this->authService->createPAT($player);

        return response()->json([
            'status' => true,
            'data' => $player,
            'token' => $token,
        ], Response::HTTP_CREATED);
    }

    /**
     * Sign in to system.
     *
     * @param  \App\Http\Requests\Auth\SignInRequest  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function signIn(SignInRequest $request)
    {
        $validated = $request->validated();

        $player = $this->authService->authenticate($validated);
        $blockedReason = $this->authService->checkMoral($player);

        if (!is_null($blockedReason)) {
            throw new HttpException(403, 'This account has been blocked! ' . $blockedReason);
        }

        $token = $this->authService->createPAT($player);

        return response()->json([
            'status' => true,
            'token' => $token,
        ], Response::HTTP_CREATED);
    }

    /**
     * Sign out of system.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signOut(Request $request)
    {
        $this->authService->revokePAT($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Signed out of the system!',
        ]);
    }
}
