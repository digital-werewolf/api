<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\PlayerService;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OAuthController extends Controller
{
    /**
     * @var array<int, string> Supported driver for OAuth providers.
     */
    private $supportedDriver;

    private AuthService $authService;

    private PlayerService $playerService;

    public function __construct(AuthService $authService, PlayerService $playerService)
    {
        $this->supportedDriver = [
            'github',
            'facebook',
            'google',
        ];

        $this->authService = $authService;
        $this->playerService = $playerService;
    }

    /**
     * Redirect to OAuth application.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect(string $driver)
    {
        if (!in_array($driver, $this->supportedDriver)) {
            throw new NotFoundHttpException();
        }

        return Socialite::driver($driver)->redirect();

    }

    /**
     * Handle OAuth callback.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(string $driver)
    {
        if (!in_array($driver, $this->supportedDriver)) {
            throw new NotFoundHttpException();
        }

        $oauth = Socialite::driver($driver)->user();
        $player = $this->playerService->existEmail($oauth->getEmail());

        if (is_null($player)) {
            $player = $this->authService->createOAuthPlayer($oauth->getEmail());
        }

        $token = $this->authService->createPAT($player);

        return response()->json([
            'status' => true,
            'token' => $token,
        ]);
    }
}
