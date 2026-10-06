<?php

namespace App\Repositories\Auth\AuthInterfaces;

use App\DTOs\Auth\LoginCredentialDTO;

interface AuthInterface
{
    /**
     * Attempt to log in and return a JWT, or null when credentials are invalid.
     */
    public function login(LoginCredentialDTO $loginCredentialDTO): ?string;
}
