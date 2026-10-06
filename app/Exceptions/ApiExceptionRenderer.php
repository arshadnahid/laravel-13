<?php

namespace App\Exceptions;

use App\Traits\ApiResponses;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Renders every exception on API routes in the standard ApiResponses format,
 * so internal details (message, file, line, trace) are never exposed.
 */
class ApiExceptionRenderer
{
    use ApiResponses;

    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*') && ! $request->expectsJson()) {
            return null;
        }

        return match (true) {
            $e instanceof ValidationException => $this->validationErrorsResponse(null, $e->errors(), $e->status),
            $e instanceof AuthenticationException => $this->unauthenticatedResponse(),
            $e instanceof HttpExceptionInterface => $this->httpErrorResponse($e->getStatusCode()),
            default => $this->sendErrors(__('apiresponse.TEXT_SERVER_ERROR'), [], Response::HTTP_INTERNAL_SERVER_ERROR),
        };
    }

    private function httpErrorResponse(int $status): JsonResponse
    {
        $message = match ($status) {
            Response::HTTP_FORBIDDEN => __('apiresponse.TEXT_FORBIDDEN'),
            Response::HTTP_NOT_FOUND => __('apiresponse.TEXT_NOT_FOUND'),
            Response::HTTP_METHOD_NOT_ALLOWED => __('apiresponse.TEXT_METHOD_NOT_ALLOWED'),
            Response::HTTP_TOO_MANY_REQUESTS => __('apiresponse.TEXT_TOO_MANY_REQUESTS'),
            default => $status >= 500 ? __('apiresponse.TEXT_SERVER_ERROR') : __('apiresponse.TEXT_ERROR'),
        };

        return $this->sendErrors($message, [], $status);
    }
}
