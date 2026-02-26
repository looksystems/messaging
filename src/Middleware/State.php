<?php

namespace Look\Messaging\Middleware;

class State
{
    protected bool $dispatched = true;
    protected bool $stopped = false;
    protected bool $canHandle = true;
    protected bool $canRelay = true;
    protected int $handled = 0;
    protected int $relayed = 0;

    // DISPATCH

    public function markAsNotDispatched(): self
    {
        $this->dispatched = false;

        return $this;
    }

    public function markAsDispatched(bool $state = true): self
    {
        $this->dispatched = $state;

        return $this;
    }

    public function wasDispatched(): bool
    {
        return $this->dispatched;
    }

    // HANDLER

    public function markAsHandled(): self
    {
        $this->handled++;

        return $this;
    }

    public function wasHandled(): bool
    {
        return $this->handled > 0;
    }

    public function canHandle(): bool
    {
        return $this->canHandle;
    }

    public function stopHandling(bool $state = true): self
    {
        $this->canHandle = !$state;

        return $this;
    }

    // RELAY

    public function markAsRelayed(): self
    {
        $this->relayed++;

        return $this;
    }

    public function wasRelayed(): bool
    {
        return $this->relayed > 0;
    }

    public function canRelay(): bool
    {
        return $this->canRelay;
    }

    public function stopRelaying(bool $state = true): self
    {
        $this->canRelay = !$state;

        return $this;
    }

    // ABORT

    public function wasStopped(): bool
    {
        return $this->stopped;
    }

    public function stop(bool $state = true): self
    {
        $this->stopped = $state;

        return $this;
    }

    public function abort(): self
    {
        $this->dispatched = false;
        $this->stopped = true;

        return $this;
    }
}
