<?php

namespace Tests\Fixtures;

use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\ProvidesMessage;
use Look\Messaging\Support\MessageUtils;

class TestMessageProvider implements ProvidesMessage
{
    protected $message;

    public function __construct($message)
    {
        $this->message = MessageUtils::cast($message);
    }

    public function toMessage(): MessageInterface
    {
        return $this->message;
    }
}
