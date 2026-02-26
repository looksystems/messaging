<?php

namespace Look\Messaging\Contracts;

interface TypeResolver
{
    public function resolve(object $message): ?MessageInterface;

    public function match(string $type, array $list): array;
}
