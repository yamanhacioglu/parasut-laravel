<?php

namespace Northlab\Parasut\Exceptions;

use Exception;

class ParasutException extends Exception
{
    /** @var array Parasut API'dan donen ham "errors" dizisi */
    protected array $errors = [];

    /** @var array Ham HTTP yanit govdesi */
    protected array $responseBody = [];

    protected int $statusCode = 0;

    public static function fromResponse(int $statusCode, array $body, ?string $message = null): static
    {
        $errors = $body['errors'] ?? [];

        $detail = $message ?? self::extractMessage($errors) ?? 'Parasut API istegi basarisiz oldu.';

        $exception = new static("[$statusCode] $detail");
        $exception->statusCode = $statusCode;
        $exception->errors = $errors;
        $exception->responseBody = $body;

        return $exception;
    }

    protected static function extractMessage(array $errors): ?string
    {
        if (empty($errors)) {
            return null;
        }

        $messages = array_map(function ($error) {
            if (is_array($error)) {
                return $error['detail'] ?? $error['title'] ?? json_encode($error);
            }

            return (string) $error;
        }, $errors);

        return implode(' | ', $messages);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getResponseBody(): array
    {
        return $this->responseBody;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
