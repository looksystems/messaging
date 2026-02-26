<?php

namespace Tests\Fixtures;

use Look\Messaging\Contracts\Handler;

class TestHandler implements Handler
{
    public $message = null;
    public $state = null;
    public int $count = 0;

    public function handle(object $message, $state)
    {
        $this->message = $message;
        $this->state = $state;
        $this->count++;
    }
}
