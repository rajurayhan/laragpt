<?php

namespace App\Exceptions;

use App\Libraries\WebApiResponse;
use ErrorException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        //\Log::info($exception);

        if ($exception instanceof AuthenticationException && $request->wantsJson()) {
            return WebApiResponse::error(401, $errors = [], 'Unauthenticated');
        }
        if ($exception instanceof AuthenticationException) {
            return WebApiResponse::error(401, $errors = [], 'Unauthenticated');
        }

        if ($exception instanceof ModelNotFoundException && $request->wantsJson()) {
            return WebApiResponse::error(404, $errors = [], 'Item not found');
        }

        if ($exception instanceof NotFoundHttpException && $request->wantsJson()) {
            return WebApiResponse::error(404, $errors = [], 'Endpoint not found');
        }

        if ($exception instanceof MethodNotAllowedHttpException && $request->wantsJson()) {
            return WebApiResponse::error(405, $errors = [], 'Requested method not allowed.');
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            return WebApiResponse::error(405, $errors = [], 'Requested method not allowed.');
        }
        if ($exception instanceof QueryException) {
            return WebApiResponse::error(405, $errors = [$exception->getMessage()], 'Query Exception');
        }
        if ($exception instanceof ErrorException) {
            return WebApiResponse::error(405, $errors = [$exception->getMessage()], 'Query Exception');
        }
        if ($exception instanceof UnauthorizedException) {
            return WebApiResponse::error(403, $errors = [$exception->getMessage()], 'Unauthorized Access!');
        }
        return parent::render($request, $exception);
    }
}
