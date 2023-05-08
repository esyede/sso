<?php

namespace Esyede\Core\Interfaces;

interface SSOServerInterface
{
    public function attach(?string $broker, ?string $token, ?string $checksum);

    public function login(?string $username, ?string $password);

    public function logout();

    public function userInfo();
}
