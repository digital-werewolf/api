<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Exception $e) {
            $message = $e->getMessage()
                ? $e->getMessage()
                : 'An unknown error occurred.';

            if ($e instanceof HttpException) {
                if ($e->getStatusCode() == 404) {
                    $message = 'Not found';
                }

                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], $e->getStatusCode());
            }
            else if (!config('app.debug')) {
                return response()->json([
                    'status' => false,
                    'message' => 'An unknown error occurred.',
                ], 500);
            }
        });
    }
}
