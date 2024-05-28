<?php

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\UnauthorizedException;
use Throwable;

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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return ResponseHelper::error('Unauthenticated', [], 401);
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof UnauthorizedException) {

            return ResponseHelper::error(trans('Unauthorized: Cannot perform this action, permission denied'), [], 401);
        }
        if ($exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return ResponseHelper::error(trans('User does not have the right roles.'), [], 403);
        }
        return parent::render($request, $exception);
    }

    public function report(Throwable $e)
    {
        if ($e instanceof \League\OAuth2\Server\Exception\OAuthServerException && $e->getCode() === 9) {
            return;
        }
        parent::report($e);
    }
}
