<?php

namespace Esyede\Core;

use Esyede\Core\Exceptions\SSOServerException;
use Esyede\Core\Interfaces\SSOServerInterface;
use Throwable;

abstract class SSOServer implements SSOServerInterface
{
    protected $brokerId;

    public function attach(?string $broker, ?string $token, ?string $checksum)
    {
        try {
            if (!$broker) {
                $this->fail('No broker id specified.', true);
            }

            if (!$token) {
                $this->fail('No token specified.', true);
            }

            if (!$checksum || $checksum != $this->generateAttachChecksum($broker, $token)) {
                $this->fail('Invalid checksum.', true);
            }

            $this->startUserSession();
            $sessionId = $this->generateSessionId($broker, $token);

            $this->saveBrokerSessionData($sessionId, $this->getSessionData('id'));
        } catch (SSOServerException $e) {
            return $this->redirect(null, ['sso_error' => $e->getMessage()]);
        }

        $this->attachSuccess();
    }

    public function login(?string $username, ?string $password)
    {
        try {
            $this->startBrokerSession();

            if (!$username || !$password) {
                $this->fail('No username and/or password provided.');
            }

            if (!$this->authenticate($username, $password)) {
                $this->fail('User authentication failed.');
            }
        } catch (SSOServerException $e) {
            return $this->returnJson(['error' => $e->getMessage()]);
        }

        $this->setSessionData('sso_user', $username);
        return $this->userInfo();
    }

    public function logout()
    {
        try {
            $this->startBrokerSession();
            $this->setSessionData('sso_user', null);
        } catch (SSOServerException $e) {
            return $this->returnJson(['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            return $this->returnJson(['error' => $e->getMessage()]);
        }

        return $this->returnJson(['success' => 'User has been successfully logged out.']);
    }

    public function userInfo()
    {
        try {
            $this->startBrokerSession();
            $username = $this->getSessionData('sso_user');

            if (!$username) {
                $this->fail('User not authenticated. Session ID: ' . $this->getSessionData('id'));
            }

            if (!$user = $this->getUserInfo($username)) {
                $this->fail('User not found.');
            }
        } catch (SSOServerException $e) {
            return $this->returnJson(['error' => $e->getMessage()]);
        }

        return $this->returnUserInfo($user);
    }

    protected function startBrokerSession()
    {
        if (isset($this->brokerId)) {
            return;
        }

        $sessionId = $this->getBrokerSessionId();

        if (!$sessionId) {
            $this->fail('Missing session key from broker.');
        }

        $savedSessionId = $this->getBrokerSessionData($sessionId);

        if (!$savedSessionId) {
            $this->fail('There is no saved session data associated with the broker session id.');
        }

        $this->startSession($savedSessionId);

        $this->brokerId = $this->validateBrokerSessionId($sessionId);
    }

    protected function validateBrokerSessionId(string $sessionId)
    {
        $matches = null;

        if (!preg_match('/^SSO-(\w*+)-(\w*+)-([a-z0-9]*+)$/', $this->getBrokerSessionId(), $matches)) {
            $this->fail('Invalid session id');
        }

        if ($this->generateSessionId($matches[1], $matches[2]) !== $sessionId) {
            $this->fail('Checksum failed: Client IP address may have changed');
        }

        return $matches[1];
    }

    protected function generateSessionId(string $brokerId, string $token)
    {
        $broker = $this->getBrokerInfo($brokerId);

        if (!$broker) {
            $this->fail('Provided broker does not exist.');
        }

        return 'SSO-' . $brokerId . '-' . $token . '-' . hash('sha256', 'session' . $token . $broker['secret']);
    }

    protected function generateAttachChecksum($brokerId, $token)
    {
        $broker = $this->getBrokerInfo($brokerId);

        if (!$broker) {
            $this->fail('Provided broker does not exist.');
        }

        return hash('sha256', 'attach' . $token . $broker['secret']);
    }

    protected function attachSuccess()
    {
        $this->redirect();
    }

    protected function fail(?string $message, bool $isRedirect = false, ?string $url = null)
    {
        if (!$isRedirect) {
            throw new SSOServerException($message);
        }

        $this->redirect($url, ['sso_error' => $message]);
    }

    abstract protected function redirect(?string $url = null, array $parameters = [], int $httpResponseCode = 307);

    abstract protected function returnJson(?array $response = null, int $httpResponseCode = 204);

    abstract protected function authenticate(string $username, string $password);

    abstract protected function getBrokerInfo(string $brokerId);

    abstract protected function getUserInfo(string $username);

    abstract protected function returnUserInfo($user);

    abstract protected function getBrokerSessionId();

    abstract protected function startUserSession();

    abstract protected function setSessionData(string $key, ?string $value = null);

    abstract protected function getSessionData(string $key);

    abstract protected function startSession(string $sessionId);

    abstract protected function saveBrokerSessionData(string $brokerSessionId, string $sessionData);

    abstract protected function getBrokerSessionData(string $brokerSessionId);
}
