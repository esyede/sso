<?php

namespace Esyede\SSO;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Esyede\SSO\Traits\SSOServerTrait;
use Esyede\SSO\Core\SSOServer;
use Esyede\SSO\Resources\UserResource;
use Throwable;

class LaravelSSOServer extends SSOServer
{
    use SSOServerTrait;

    protected function redirect(?string $url = null, array $parameters = [], int $httpResponseCode = 307)
    {
        $url = $url ? $url : urldecode(request()->get('return_url', null));
        $query = '';

        if (!empty($parameters)) {
            $query .= '?' . (parse_url($url, PHP_URL_QUERY) ? '&' : '');
            $query .= http_build_query($parameters);
        }

        app()->abort($httpResponseCode, '', ['Location' => $url . $query]);
    }

    protected function returnJson(?array $response = null, int $httpResponseCode = 200)
    {
        return response()->json($response, $httpResponseCode);
    }

    protected function authenticate(string $phone, string $password)
    {
        if (config('withoutPassword') == true) {
            $user = config('sso.users_model')::where('phone', $phone)->firstOrFail();
            return (bool) Auth::loginUsingId($user->id);
        }

        if (!Auth::attempt(['phone' => $phone, 'password' => $password])) {
            return false;
        }

        $this->startSession($this->getBrokerSessionData($this->getBrokerSessionId()));
        return true;
    }

    protected function getBrokerInfo(string $brokerId)
    {
        try {
            $broker = config('sso.brokers_model')::where('name', $brokerId)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            // report($e);
            return null;
        } catch (Throwable $e) {
            // report($e);
            return null;
        }

        return $broker;
    }

    protected function getUserInfo(string $phone)
    {
        try {
            if (config('sso.use_relationship') == true) {
                $user = config('sso.users_model')::where('phone', $phone)->with("config('sso.relation_name')")->firstOrFail();
            } else {
                $user = config('sso.users_model')::where('phone', $phone)->firstOrFail();
            }
        } catch (ModelNotFoundException $e) {
            // report($e);
            return null;
        } catch (Throwable $e) {
            // report($e);
            return null;
        }

        return $user;
    }

    protected function returnUserInfo($user)
    {
        return new UserResource($user);
    }

    protected function getBrokerSessionId()
    {
        return request()->bearerToken();
    }

    protected function startUserSession()
    {
        // Session must be started by middleware.
    }

    protected function setSessionData(string $key, ?string $value = null)
    {
        if (!$value) {
            Session::forget($key);
        } else {
            Session::put($key, $value);
        }
    }

    protected function getSessionData(string $key)
    {
        return ($key === 'id') ? Session::getId() : Session::get($key, null);
    }

    protected function startSession(string $sessionId)
    {
        Session::setId($sessionId);
        Session::start();
    }

    protected function saveBrokerSessionData(string $brokerSessionId, string $sessionData)
    {
        Cache::put('broker_session:' . $brokerSessionId, $sessionData, now()->addHour());
    }

    protected function getBrokerSessionData(string $brokerSessionId)
    {
        return Cache::get('broker_session:' . $brokerSessionId);
    }
}
