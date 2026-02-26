<?php

namespace Look\Messaging\Middleware;

use Closure;
use Look\Messaging\MessageBus;
use Psr\Container\ContainerInterface;

class Middleware
{
    protected MessageBus $bus;
    protected Closure $closure;
    protected State $state;

    protected $original = null;

    // MIDDLEWARE

    public function prepare(array $args, Closure $next)
    {
        [$message, $bus, $state] = $args;

        $this->bus = $bus;
        $this->closure = $next;
        $this->state = $state;

        if (is_null($this->original)) {
            $this->original = $message;
        }

        return $this->handle($message);
    }

    public function handle($message)
    {
        return $this->next($message);
    }

    protected function next($message)
    {
        $next = $this->closure;

        return $next([$message, $this->bus, $this->state]);
    }

    // CONTAINER

    protected function container(): ContainerInterface
    {
        return $this->bus->getContainer();
    }
}
