<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Helpers\TranslationHelper;
use Illuminate\Auth\AuthenticationException;

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
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson())
        {
            return response()->json(['success'=>false, 'code'=>401, 'message'=>TranslationHelper::translate('un_authenticated_access')], 401);
        }

        return redirect('/');
    }

        /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($this->isHttpException($exception))
        {
            if ($exception->getStatusCode() == 404)
            {
                if ($request->expectsJson())
                {
                    return response()->json(['success' => false, 'message'=>TranslationHelper::translate('try_again_later'), 'code'=>200]);
                }
                else
                {
                    // return redirect('/');
                }
            }
        }
        return parent::render($request, $exception);
    }
}
