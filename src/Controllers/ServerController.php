<?php

namespace Esyede\SSO\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Esyede\SSO\Events\ApiSSOLogoutEvent;
use Esyede\SSO\LaravelSSOServer;
use Esyede\SSO\Traits\SSOControllerTrait;

class ServerController extends BaseController
{
    use SSOControllerTrait;

    public function attach(Request $request, LaravelSSOServer $server)
    {
        $server->attach(
            $request->get('broker', null),
            $request->get('token', null),
            $request->get('checksum', null)
        );
    }

    public function login(Request $request, LaravelSSOServer $server)
    {
        return $server->login(
            $request->get('phone', null),
            $request->get('password', null)
        );
    }

    public function logout(Request $request, LaravelSSOServer $server)
    {
        $result = $server->logout();

        if (config('sso.api.enabled')) {
            $response = $result->getData(true);

            if (isset($response['success'])) {
                event(new ApiSSOLogoutEvent($request));
            }
        }

        return $result;
    }

    public function userInfo(LaravelSSOServer $server)
    {
        return $server->userInfo();
    }
}
