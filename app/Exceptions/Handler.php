<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $message = 'Internal Server Error';
        $statusCode = 500;
        if ($exception instanceof HttpException) {
            $class = get_class($exception);
            $statusCode = $exception->getStatusCode();
            $statusMap = ['500' => 'Internal Server Error', '404' => 'Not Found',
                '403' => 'Forbidden', '401' => 'Unauthorized', '400' => 'Bad Request', ];
            if (array_key_exists($exception->getStatusCode(), $statusMap)) {
                $message = $statusMap[$exception->getStatusCode()];
            } else {
                $message = substr($class, strrpos($class, '\\') + 1);
            }
        } elseif ($exception instanceof \SquareConnect\ApiException) {
            $message = $exception->getResponseBody()->errors[0]->detail;
        } elseif ($exception instanceof \Exception) {
            $message = $exception->getMessage();
        } else {
            return parent::render($request, $exception);
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'error', 'error' => $message], $statusCode);
        } elseif (config('app.debug') == false) {
            return response(view('errors.generic',
                ['error_code' => $statusCode, 'error_message' => $message]), $statusCode);
        } else {
            return parent::render($request, $exception);
        }
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
