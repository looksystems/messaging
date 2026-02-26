<?php

namespace Look\Messaging\Support;

use ArrayObject;
use Closure;
use Exception;
use Look\Messaging\Contracts\MessageBus;

class ListOfMessages extends ArrayObject
{
    protected MessageBus $bus;

    public function __construct(MessageBus $bus, array $messages = [])
    {
        $this->bus = $bus;

        parent::__construct($messages);
    }

    public function isEmpty(): bool
    {
        return !$this->count();
    }

    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    public function dispatch(?Closure $exceptionHandler = null): self
    {
        foreach ($this as $message) {
            try {
                $this->bus->dispatch($message);
            } catch (Exception $exception) {
                if ($exceptionHandler) {
                    $exceptionHandler($exception, $message);
                }
            }
        }

        return $this;
    }
}
