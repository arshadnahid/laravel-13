<?php

namespace App\Exceptions;

use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserNotFoundException extends Exception
{
    use ApiResponses;

    public function render(): JsonResponse
    {
        return $this->sendErrors(__('apiresponse.TEXT_INVALID_CREDENTIALS'), [], Response::HTTP_UNAUTHORIZED);
    }
}
