<?php

namespace Tests\Fixtures;

use Look\Messaging\Contracts\Transformer;

class TestTransformer implements Transformer
{
    public $message;
    public int $count = 0;

    public function transform(object $message): object
    {
        $this->message = $message;
        $this->count++;

        return $message;
    }
}
