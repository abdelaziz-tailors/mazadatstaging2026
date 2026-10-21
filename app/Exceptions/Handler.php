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

    /**
     * The "auth" middleware alias is only ever used as "auth:api" in this
     * app (routes/api/user.php) — the admin dashboard has its own separate
     * AuthAdmin middleware that never throws AuthenticationException. So
     * every caller reaching this is an API client; always return JSON,
     * regardless of $request->expectsJson() (many real clients — Postman
     * with no explicit Accept header, a browser tab opened directly on a
     * download link — don't set it, and used to silently 302-redirect here
     * instead of getting a usable error).
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json(['success'=>false, 'code'=>401, 'message'=>TranslationHelper::translate('un_authenticated_access')], 401);
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
        $field = $exception instanceof \Illuminate\Database\QueryException ? self::identityConflict($exception) : null;
        if ($field) {
            $message = TranslationHelper::translate($field === 'phone' ? 'phone_number_exists' : 'email_address_exists');
            if ($request->is('api/*')) {
                $status = $request->is('api/user/register') ? 422 : 200;
                return response()->json(['success' => false, 'code' => $status, 'message' => $message], $status);
            }
            return redirect()->back()->withInput($request->except($this->dontFlash))->withErrors([$field => $message]);
        }
        if ($this->isHttpException($exception))
        {
            if ($exception->getStatusCode() == 404)
            {
                if ($request->expectsJson())
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'API endpoint not found: '.$request->method().' '.$request->path(),
                        'code' => 404,
                    ], 404);
                }
                else
                {
                    // return redirect('/');
                }
            }
        }
        return parent::render($request, $exception);
    }

    public static function identityConflict(\Illuminate\Database\QueryException $exception): ?string
    {
        $info = $exception->errorInfo;
        if (! (($info[0] ?? null) === '23000' && in_array((int) ($info[1] ?? 0), [19, 1062], true))) {
            return null;
        }
        foreach (['phone', 'email'] as $field) {
            if (preg_match('/registration_'.$field.'_(?:unique|key)|(?:users|admins)_'.$field.'_unique/', $exception->getMessage())) {
                return $field;
            }
        }
        return null;
    }
}
