<?php

namespace Esyede\SSO\Core;

use Esyede\SSO\Core\Interfaces\SSOBrokerInterface;

abstract class SSOBroker implements SSOBrokerInterface
{
    protected $ssoServerUrl;
    protected $brokerName;
    protected $brokerSecret;
    protected $userInfo;
    protected $token;

    public function __construct()
    {
        $this->setOptions();
        $this->saveToken();
    }

    public function attach()
    {
        $this->redirect($this->generateCommandUrl('attach', [
            'return_url' => $this->getCurrentUrl(),
            'broker' => $this->brokerName,
            'token' => $this->token,
            'checksum' => hash('sha256', 'attach' . $this->token . $this->brokerSecret)
        ]));
    }

    public function getUserInfo()
    {
        if (!isset($this->userInfo) || empty($this->userInfo)) {
            $this->userInfo = $this->makeRequest('GET', 'userInfo');
        }

        return $this->userInfo;
    }

    public function login(string $phone, string $password)
    {
        $this->userInfo = $this->makeRequest('POST', 'login', compact('phone', 'password'));
        return (!isset($this->userInfo['error']) && isset($this->userInfo['data']['id']));
    }

    public function logout()
    {
        $this->makeRequest('POST', 'logout');
    }

    protected function generateCommandUrl(string $command, array $parameters = [])
    {
        $parameters = empty($parameters) ? '' : '?' . http_build_query($parameters);
        return $this->ssoServerUrl . '/sso/' . $command . $parameters;
    }

    protected function getSessionId()
    {
        $checksum = hash('sha256', 'session' . $this->token . $this->brokerSecret);
        return 'SSO-' . $this->brokerName . '-' . $this->token . '-' . $checksum;
    }

    abstract protected function setOptions();

    abstract protected function saveToken();

    abstract protected function deleteToken();

    abstract protected function makeRequest(string $method, string $command, array $parameters = []);

    abstract protected function redirect(string $url, array $parameters = [], int $httpResponseCode = 307);

    abstract protected function getCurrentUrl();
}
