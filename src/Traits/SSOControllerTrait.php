<?php

namespace Esyede\SSO\Traits;

use Esyede\SSO\Events\ApiSSOLoginEvent;
use Illuminate\Http\Request;
use Esyede\SSO\LaravelSSOServer;
use Esyede\SSO\Resources\UserResource;
use stdClass;

trait SSOControllerTrait
{
    public function userInfoMulti(LaravelSSOServer $server)
    {
        return $server->userInfoMulti();
    }

    public function loginMulti(Request $request, LaravelSSOServer $server)
    {
        $key = $request->get('key');

        return $server->loginMulti(
            $request->get($key, null),
            $request->get('password', null),
            $request->get('key', null)
        );
    }

    private function response($data = '', $error_code = 0, $error_message = '')
    {
        return response()->json([
            'code' => 200,
            'error_message' => $error_message,
            'error_code' => $error_code,
            'data' => empty($data) ? new stdClass() : $data,
            'message' => '',
        ]);
    }

    public function check(Request $request, LaravelSSOServer $server)
    {
        $flag = $request->input('flag');

        if (empty($flag)) {
            return $this->response([], 204, 'the login key required.');
        }

        $functions = config('sso.api.getUserId');
        $callable = explode('@', $functions['uses']);
        $userId = call_user_func([$callable[0], $callable[1]], ['flag' => $flag]);

        if (empty($userId)) {
            return $this->response([], 201, 'cannot find user_id or the user has not logged in.');
        }

        // get User
        $user = config('sso.users_model')::find($userId);

        if (empty($user)) {
            return $this->response([], 202, 'User Not Found');
        }

        // handle login
        $functions = config('sso.api.getPassword');
        $callable = explode('@', $functions['uses']);
        $password = call_user_func([$callable[0], $callable[1]], $user);

        if (config('sso.multi_enabled')) {
            $result = $server->loginMulti($user->email, $password, 'email');
        } else {
            $result = $server->login($user->email, $password);
        }

        // sso-login hack
        event(new ApiSSOLoginEvent($user, $flag));

        $functions = config('sso.api.getMerged');
        $callable = explode('@', $functions['uses']);
        $userId = call_user_func([$callable[0], $callable[1]], ['flag' => $flag]);

        if ($result instanceof UserResource) {
            $result = $result->toArray($request);
        }

        $result = empty($merged) ? $result : array_merge($merged, (array) $result);

        return $this->response($result);
    }
}
