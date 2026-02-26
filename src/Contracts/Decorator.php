<?php

namespace Look\Messaging\Contracts;

interface Decorator
{
    public function decorate(object $message): object;
}
