<?php

namespace Look\Messaging\Serializers;

use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\Serializer;
use Look\Messaging\Support\MessageUtils;

class StandardSerializer implements Serializer
{
    // INSTANTIATION

    public static function make(): StandardSerializer
    {
        return new self;
    }

    // SERIALIZER

    public function serialize(MessageInterface $message): mixed
    {
        return MessageUtils::toJson($message);
    }

    public function unserialize(mixed $data): MessageInterface
    {
        return MessageUtils::cast($data);
    }
}
