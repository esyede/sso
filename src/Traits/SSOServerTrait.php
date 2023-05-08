<?php

namespace Esyede\SSO\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Esyede\Core\Exceptions\SSOServerException;

trait SSOServerTrait
{
    public function loginMulti(?string $keyValue, ?string $password, ?string $key)
    {
        try {
            $this->startBrokerSession();

            if (!$keyValue || !$password) {
                $this->fail('No keyVale and/or password provided.');
            }

            if (!$userId = $this->authenticateMulti($keyValue, $password, $key)) {
                $this->fail('User authentication failed.');
            }
        } catch (SSOServerException $e) {
            return $this->returnJson(['error' => $e->getMessage()]);
        }

        $this->setSessionData('sso_user', $userId);

        return $this->userInfoMulti();
    }

    public function userInfoMulti()
    {
        try {
            $this->startBrokerSession();

            $userId = $this->getSessionData('sso_user');

            if (!$userId) {
                $this->fail('User not authenticated. Session ID: ' . $this->getSessionData('id'));
            }

            if (!$user = $this->getUserInfoMulti($userId)) {
                $this->fail('User not found.');
            }
        } catch (SSOServerException $e) {
            return $this->returnJson(['error' => $e->getMessage()]);
        }

        return $this->returnUserInfo($user);
    }

    protected function getUserInfoMulti(string $userId)
    {
        try {
            if(config('sso.use_relationship') == true) {
                $user = config('sso.users_model')::where('id', $userId)->with("config('sso.relation_name')")->firstOrFail();
            } else {
                $user = config('sso.users_model')::where('id', $userId))->firstOrFail();
            }
        } catch (ModelNotFoundException $e) {
            return null;
        }

        return $user;
    }

    protected function authenticateMulti(string $value, string $password, string $key)
    {
        if (!Auth::attempt([$key => $value, 'password' => $password])) {
            return false;
        }

        $sessionId = $this->getBrokerSessionId();
        $savedSessionId = $this->getBrokerSessionData($sessionId);
        $this->startSession($savedSessionId);

        return Auth::id();
    }
}
