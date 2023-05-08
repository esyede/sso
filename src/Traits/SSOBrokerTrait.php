<?php

namespace Esyede\SSO\Traits;

use Illuminate\Http\Request;

trait SSOBrokerTrait
{
    public function logoutWithCookie(Request $request)
    {
        $cookies = $request->cookie();
        $this->makeRequest('POST', 'logout', [], $cookies);
    }

    public function check($flag = '')
    {
        $this->userInfo = $this->makeRequest('POST', 'check', compact('flag'));
        return $this->userInfo;
    }

    public function loginMulti(string $keyValue, string $password, string $key)
    {
        $this->userInfo = $this->makeRequest('POST', 'loginMulti', [
            $key => $keyValue,
            'password' => $password,
            'key' => $key
        ]);

        return (!isset($this->userInfo['error']) && isset($this->userInfo['data']['id']));
    }

    public function getUserInfoMulti()
    {
        if (!isset($this->userInfo) || !$this->userInfo) {
            $this->userInfo = $this->makeRequest('GET', 'userInfoMulti');
        }

        return $this->userInfo;
    }

    public function handleLogin($credentialUuid, $credentialPassword, $loginKey = '')
    {
        if (config('sso.multi_enabled')) {
            return $this->loginMulti($credentialUuid, $credentialPassword, $loginKey);
        }

        return $this->login($credentialUuid, $credentialPassword);
    }

    public function handleGetUserInfo()
    {
        if (config('sso.multi_enabled')) {
            return $this->getUserInfoMulti();
        } else {
            return $this->getUserInfo();
        }
    }
}
