<?php

namespace Tests\Fixtures;

use Look\Messaging\Middleware\Middleware;

class TestMiddleware extends Middleware
{
    public $message = null;
    public int $count = 0;

    public function handle($message)
    {
        $this->message = $message;
        $this->count++;

        return $this->next($message);
    }
}
