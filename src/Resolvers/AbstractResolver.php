<?php

namespace Look\Messaging\Resolvers;

use DateTime;
use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\TypeResolver;
use Look\Messaging\Envelope;
use Look\Messaging\Message;
use Ramsey\Uuid\Uuid;

abstract class AbstractResolver implements TypeResolver
{
    // PAYLOAD

    public function resolve(object $message): ?MessageInterface
    {
        $type = $this->type($message);
        if (!$type) {
            return null;
        }

        return Message::make(
            $type,
            $this->payload($message),
            $this->id($message),
            $this->version($message),
            $this->timestamp($message),
        );
    }

    public function id(object $message): string|int
    {
        return (string) Uuid::uuid4();
    }

    public function type(object $message): ?string
    {
        return null;
    }

    public function version(object $message): string
    {
        return 1;
    }

    public function timestamp(object $message): DateTime
    {
        return new DateTime;
    }

    public function payload(object $message): ?object
    {
        return $message;
    }

    public function envelope(object $message): ?Envelope
    {
        return null;
    }

    public function isTest(object $message): bool
    {
        return false;
    }
}
