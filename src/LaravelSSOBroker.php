<?php

namespace Esyede\SSO;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Esyede\SSO\Exceptions\MissingConfigurationException;
use Esyede\SSO\Core\SSOBroker;
use Esyede\SSO\Traits\SSOBrokerTrait;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Cookie\CookieJar;

class LaravelSSOBroker extends SSOBroker
{
    use SSOBrokerTrait;

    protected function generateCommandUrl(string $command, array $parameters = [])
    {
        $parameters = empty($parameters) ? '' : '?' . http_build_query($parameters);
        return $this->ssoServerUrl . '/api/sso/' . $command . $parameters;
    }

    protected function setOptions()
    {
        $this->ssoServerUrl = config('sso.serverUrl', null);
        $this->brokerName = config('sso.broker_name', null);
        $this->brokerSecret = config('sso.broker_secret', null);

        if (!$this->ssoServerUrl || !$this->brokerName || !$this->brokerSecret) {
            throw new MissingConfigurationException('Missing configuration values.');
        }
    }

    protected function saveToken()
    {
        if (isset($this->token) && $this->token) {
            return;
        }

        if ($this->token = Cookie::get($this->getCookieName(), null)) {
            return;
        }

        $this->token = Str::random(40);
        Cookie::queue(Cookie::make($this->getCookieName(), $this->token, 60));

        $this->attach();
    }

    protected function deleteToken()
    {
        $this->token = null;
        Cookie::forget($this->getCookieName());
    }

    protected function makeRequest(string $method, string $command, array $parameters = [], array $cookies = [])
    {
        $commandUrl = $this->generateCommandUrl($command);
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getSessionId(),
        ];

        switch ($method) {
            case 'POST':
                $body = ['form_params' => $parameters];
                break;

            case 'GET':
                $body = ['query' => $parameters];
                break;

            default:
                $body = [];
                break;
        }

        if (!empty($cookies)) {
            $body[RequestOptions::COOKIES] = CookieJar::fromArray($cookies, 'tripay.co.id');
        }

        $client = new Client;
        $response = $client->request($method, $commandUrl, $body + ['headers' => $headers]);

        return json_decode($response->getBody(), true);
    }

    protected function redirect(string $url, array $parameters = [], int $httpResponseCode = 307)
    {
        $query = '';

        if (!empty($parameters)) {
            $query = '?';

            if (parse_url($url, PHP_URL_QUERY)) {
                $query = '&';
            }

            $query .= http_build_query($parameters);
        }

        app()->abort($httpResponseCode, '', ['Location' => $url . $query]);
    }

    protected function getCurrentUrl()
    {
        return URL::full();
    }

    protected function getCookieName()
    {
        return 'sso_token_' . preg_replace('/[_\W]+/', '_', strtolower($this->brokerName));
    }
}
