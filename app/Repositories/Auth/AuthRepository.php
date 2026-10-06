<?php

namespace App\Repositories\Auth;

use App\DTOs\Auth\LoginCredentialDTO;
use App\Repositories\Auth\AuthInterfaces\AuthInterface;

class AuthRepository implements AuthInterface
{
    public function login(LoginCredentialDTO $loginCredentialDTO): ?string
    {
        return auth()->attempt($loginCredentialDTO->toArray()) ?: null;
    }
}
