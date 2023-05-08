<?php

namespace Esyede\SSO\Core\Interfaces;

interface SSOBrokerInterface
{
    public function attach();

    public function getUserInfo();

    public function login(string $username, string $password);

    public function logout();
}
