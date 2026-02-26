<?php

namespace Tests\Fixtures;

class Invokable
{
    public $message;
    public int $count = 0;

    public function __invoke($message)
    {
        $this->message = $message;
        $this->count++;
    }
}
