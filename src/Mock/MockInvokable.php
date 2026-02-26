<?php

namespace Look\Messaging\Mock;

use Look\Messaging\Support\MessageHistory;

class MockInvokable
{
    protected MessageHistory $history;
    protected $proxy = null;

    public function __construct($proxy = null)
    {
        $this->history = new MessageHistory;

        if ($proxy) {
            $this->proxy($proxy);
        }
    }

    public function proxy($proxy): self
    {
        $this->proxy = $proxy;

        return $this;
    }

    public function __invoke(...$args)
    {
        if (isset($args[0])) {
            $this->history->push($args[0]);
        }

        if ($this->proxy) {
            return $this->callProxy(...$args);
        }
    }

    protected function callProxy(...$args)
    {
        $proxy = $this->proxy;
        if (is_callable($proxy)) {
            return $proxy(...$args);
        }
    }

    public function called(): bool
    {
        return (bool) $this->history->count();
    }

    public function messages(): MessageHistory
    {
        return $this->history;
    }
}
