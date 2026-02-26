<?php

namespace Look\Messaging\Contracts;

interface Transport
{
    public function send(MessageInterface $message, ?array $args = null): ?bool;
    public function receive(?array $args = null): array;
}
