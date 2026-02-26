<?php

namespace Tests\Fixtures;

use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\Relay;

class TestRelay implements Relay
{
    public $message = null;
    public $state = null;
    public int $count = 0;

    public function relay(MessageInterface $message, $state)
    {
        $this->message = $message;
        $this->state = $state;
        $this->count++;
    }
}
