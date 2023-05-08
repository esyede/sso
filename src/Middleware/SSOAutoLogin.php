<?php

namespace Esyede\SSO\Middleware;

use Closure;
use Illuminate\Http\Request;
use Esyede\SSO\LaravelSSOBroker;

class SSOAutoLogin
{
    public function handle(Request $request, Closure $next)
    {
        $broker = new LaravelSSOBroker();
        $response = $broker->handleGetUserInfo();

        if (!isset($response['data']) && !auth()->guest()) {
            return $this->logout($request);
        }

        if (
            isset($response['error'])
            && strpos($response['error'], 'There is no saved session data associated with the broker session id') !== false
        ) {
            return $this->clearSSOCookie($request);
        }

        if (isset($response['data']) && (auth()->guest() || auth()->user()->id !== $response['data']['id'])) {
            auth()->loginUsingId($response['data']['id']);
        }

        return $next($request);
    }

    protected function clearSSOCookie(Request $request)
    {
        return redirect($request->fullUrl())->cookie(cookie('sso_token_' . config('sso.broker_name')));
    }

    protected function logout(Request $request)
    {
        auth()->logout();
        return redirect($request->fullUrl());
    }
}
