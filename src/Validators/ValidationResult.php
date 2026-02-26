<?php

namespace Look\Messaging\Validators;

use Exception;
use Look\Messaging\Exceptions\InvalidMessageException;
use Look\Messaging\Exceptions\InvalidSchemaException;
use Look\Messaging\Exceptions\MessageBusException;
use Look\Messaging\Exceptions\NoSchemaException;

class ValidationResult
{
    // INSTANTIATION

    public static function valid(): self
    {
        return new self;
    }

    public static function noSchema(string $message = 'No schema', array $explanation = []): self
    {
        return new self(
            NoSchemaException::make($message, $explanation)
        );
    }

    public static function invalidSchema(string $message = 'Invalid schema', array $explanation = []): self
    {
        return new self(
            InvalidSchemaException::make($message, $explanation)
        );
    }

    public static function invalidMessage(string $message = 'Invalid message', array $explanation = []): self
    {
        return new self(
            InvalidMessageException::make($message, $explanation)
        );
    }

    public static function make($result): self
    {
        if ($result instanceof self) {
            return $result;
        }

        if (is_null($result)) {
            return self::noSchema();
        }

        if ($result === true) {
            return self::valid();
        }

        if ($result === false) {
            return self::invalidMessage();
        }

        if (is_string($result)) {
            return self::invalidMessage($result);
        }

        if ($result instanceof MessageBusException) {
            return new self($result);
        }

        if ($result instanceof Exception) {
            return self::invalidSchema($result->getMessage());
        }

        return self::invalidSchema('Unexpected result');
    }

    public function __construct(
        protected ?MessageBusException $exception = null
    ) {}

    // RESULT

    public function type(): string
    {
        if (!$this->exception) {
            return 'valid';
        }

        if ($this->isInvalidMessage()) {
            return 'invalidMessage';
        }

        if ($this->isNoSchema()) {
            return 'noSchema';
        }

        return 'invalidSchema';
    }

    public function isValid(): bool
    {
        return is_null($this->exception);
    }

    public function isInvalidMessage(): bool
    {
        return $this->exception instanceof InvalidMessageException;
    }

    public function isInvalidSchema(): bool
    {
        return $this->exception instanceof InvalidSchemaException;
    }

    public function isNoSchema(): bool
    {
        return $this->exception instanceof NoSchemaException;
    }

    public function getMessage(): ?string
    {
        return $this->exception?->getMessage();
    }

    public function getExplanation(): array
    {
        return $this->exception?->getExplanation() ?? [];
    }

    public function getExplanationAsString(): ?string
    {
        return $this->exception?->getExplanationAsString() ?? null;
    }

    public function getException(): MessageBusException
    {
        return $this->exception;
    }
}
