<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateEmailRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateUsernameRequest;
use App\Services\ProfileService;

class ProfileController extends Controller
{
    private ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;

        $this->middleware('auth:sanctum');
    }

    /**
     * Show player profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json([
            'data' => auth()->user(),
        ]);
    }

    /**
     * Update player's username.
     *
     * @param \App\Http\Requests\Profile\UpdateUsernameRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUsername(UpdateUsernameRequest $request)
    {
        $status = $this->profileService->updateUsername(
            $request->user(),
            $request->safe()->only('username')['username'],
        );

        return response()->json([
            'status' => $status,
            'message' => 'Your username has been updated!',
        ]);
    }

    /**
     * Update player's email.
     *
     * @param \App\Http\Requests\Profile\UpdateEmailRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEmail(UpdateEmailRequest $request)
    {
        $status = $this->profileService->updateEmail(
            $request->user(),
            $request->safe()->only('email')['email'],
        );

        return response()->json([
            'status' => $status,
            'message' => 'Your email has been updated!',
        ]);
    }

    /**
     * Update player's password.
     *
     * @param \App\Http\Requests\Profile\UpdatePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $status = $this->profileService->updatePassword(
            $request->user(),
            $request->safe()->only('password')['password'],
        );

        return response()->json([
            'status' => $status,
            'message' => 'Your password has been updated!',
        ]);
    }
}
