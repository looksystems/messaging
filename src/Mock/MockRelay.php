<?php

namespace Look\Messaging\Mock;

use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\Relay;

class MockRelay extends MockInvokable implements Relay
{
    public function relay(MessageInterface $message, $state)
    {
        return $this->__invoke($message, $state);
    }

    protected function callProxy(...$args)
    {
        $proxy = $this->proxy;
        if ($proxy instanceof Relay) {
            return $proxy->relay(...$args);
        }
        if (is_callable($proxy)) {
            return $proxy(...$args);
        }
    }
}
