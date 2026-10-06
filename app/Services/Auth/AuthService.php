<?php

namespace App\Services\Auth;

use App\DTOs\Auth\LoginCredentialDTO;
use App\Exceptions\UserNotFoundException;
use App\Repositories\Auth\AuthInterfaces\AuthInterface;

class AuthService
{
    public function __construct(
        private readonly AuthInterface $auth
    ) {}

    /**
     * @throws UserNotFoundException
     */
    public function login(LoginCredentialDTO $loginCredentialDTO): string
    {
        return $this->auth->login($loginCredentialDTO) ?? throw new UserNotFoundException();
    }
}
