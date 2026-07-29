<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * This app is API-only where this guard is concerned ("auth" is only ever
     * used as "auth:api" — the dashboard has its own separate AuthAdmin
     * middleware, not this class) and has no named "login" route to redirect
     * to. Returning null here means the base middleware always throws a
     * plain AuthenticationException with no redirect target, which
     * App\Exceptions\Handler::unauthenticated() turns into a clean JSON 401
     * regardless of whether the request set an explicit Accept header.
     *
     * Previously this called route('login') whenever the request didn't
     * "expectsJson()" (true for a lot of real clients — e.g. Postman with no
     * explicit Accept header, or a browser tab opened directly on a PDF
     * download link) — since no "login" route exists, that threw
     * RouteNotFoundException, which isn't an AuthenticationException, so it
     * bypassed the JSON-401 handler entirely and surfaced as a raw 500.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        return null;
    }
}
