<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'message'   => 'Metode permintaan data tidak diizinkan',
                'status'    => false,
                'success'   => false,
                'data'      => null
            ], 405);
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'message'   => $e->getMessage(),
                'status'    => false,
                'success'   => false,
                'data'      => null
            ], $e->getStatusCode());
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            Log::error('Exception caught in render method: ' . $exception->getMessage());

            return response()->json([
                'status'    => false,
                'success'   => false,
                'message'   => $exception->getMessage(),
                'data'      => null
            ], $this->isHttpException($exception) ? $exception->getCode() : 500);
        }

        return parent::render($request, $exception);
    }
}
