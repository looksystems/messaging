<?php

namespace Look\Messaging\Resolvers;

use Look\Messaging\Support\Wildcard;

class MessageProperty extends AbstractResolver
{
    public function __construct(
        private readonly string $property = '_type',
        private readonly string $delimiter = '.',
        private readonly string $wildcard = '*',
        private readonly bool $unset = true,
    ) {}

    public function type(object $message): ?string
    {
        $property = $this->property;
        if (isset($message->$property)) {
            return (string) $message->$property;
        }

        return null;
    }

    public function payload(object $message): ?object
    {
        $payload = clone $message;

        if (!$this->unset) {
            return $payload;
        }

        $property = $this->property;
        if (isset($payload->$property)) {
            unset($payload->$property);
        }

        return $payload;
    }

    public function match(string $type, array $list): array
    {
        return Wildcard::findByKey($type, $list, $this->delimiter, $this->wildcard);
    }
}
