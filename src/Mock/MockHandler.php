<?php

namespace Look\Messaging\Mock;

use Look\Messaging\Contracts\Handler;

class MockHandler extends MockInvokable implements Handler
{
    public function handle(object $message, $state)
    {
        return $this->__invoke($message, $state);
    }

    protected function callProxy(...$args)
    {
        $proxy = $this->proxy;
        if ($proxy instanceof Handler) {
            return $proxy->handle(...$args);
        }

        if (is_callable($proxy)) {
            return $proxy(...$args);
        }
    }
}
