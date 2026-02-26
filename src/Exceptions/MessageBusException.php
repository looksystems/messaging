<?php

namespace Look\Messaging\Exceptions;

use Exception;

class MessageBusException extends Exception
{
    public static function make(?string $message = null, ?array $explanation = null): MessageBusException
    {
        return (new static($message))->setExplanation($explanation);

    }

    protected array $explanation = [];

    public function setExplanation(array $explanation): self
    {
        $this->explanation = $explanation;

        return $this;
    }

    public function getExplanation(): array
    {
        return $this->explanation;
    }

    public function getExplanationAsString(): string
    {
        return var_export($this->explanation, true);
    }

    public function hasExplanation(): bool
    {
        return !empty($this->explanation);
    }
}
