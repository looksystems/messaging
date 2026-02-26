<?php

namespace Look\Messaging\Contracts;

use DateTime;
use Look\Messaging\Envelope;

interface MessageInterface
{
    public function id(): string|int;
    public function type(): string;
    public function version(): string;
    public function timestamp(): DateTime;
    public function payload(): object;
    public function envelope(): Envelope;
    public function isTest(): bool;
    public function system(): ?string;

    public function getOriginal(): mixed;
}
