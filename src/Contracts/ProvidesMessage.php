<?php

namespace Look\Messaging\Contracts;

interface ProvidesMessage
{
    public function toMessage(): MessageInterface;
}
