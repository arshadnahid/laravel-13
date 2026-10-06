<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\TokenResource;
use App\Http\Resources\Auth\UserResource;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponses;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(private readonly AuthService $authService)
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login($request->toDTO());

        return $this->sendResponse('Login successful', new TokenResource($token));
    }

    /**
     * Get the authenticated User.
     */
    public function me(): JsonResponse
    {
        return $this->sendResponse(null, ['user' => new UserResource(auth()->user())]);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return $this->sendResponse('Successfully logged out', null);
    }

    /**
     * Refresh a token.
     */
    public function refresh(): JsonResponse
    {
        return $this->sendResponse('Token refreshed', new TokenResource(auth()->refresh()));
    }
}
