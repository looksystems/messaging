<?php

namespace Look\Messaging\Contracts;

interface Serializer
{
    public function serialize(MessageInterface $message): mixed;
    public function unserialize(mixed $data): MessageInterface;
}
