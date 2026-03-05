<?php

namespace MaplePHP\Core\Exceptions;

use Throwable;

class HttpException extends \RuntimeException
{
    public function __construct(
        readonly int $status,
        string $message = "",
        readonly array $props = [],
        readonly ?Throwable $previous = null
    ) {
        parent::__construct($message, $status, $previous);
        $this->message = $message;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getReasonPhrase(): string
    {
        return $this->message;
    }

    public function getProps(): array
    {
        return $this->props;
    }
}