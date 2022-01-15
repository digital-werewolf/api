<?php

namespace App\Http\Middleware;

use App\Services\LockService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AllowedAction
{
    private LockService $lockService;

    public function __construct(LockService $lockService)
    {
        $this->lockService = $lockService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $action
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $action)
    {
        $lockedReason = $this->lockService->isLocked($request->user(), $action);

        if (!is_null($lockedReason)) {
            throw new HttpException(403, $lockedReason);
        }

        return $next($request);
    }
}
