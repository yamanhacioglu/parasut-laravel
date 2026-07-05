<?php

namespace Northlab\Parasut\Exceptions;

class ParasutRateLimitException extends ParasutException
{
    protected ?int $retryAfter = null;

    public function setRetryAfter(?int $seconds): static
    {
        $this->retryAfter = $seconds;

        return $this;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
