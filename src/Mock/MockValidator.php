<?php

namespace Look\Messaging\Mock;

use Look\Messaging\Contracts\Validator;

class MockValidator extends MockInvokable implements Validator
{
    public function validate(object $message, $type): ?bool
    {
        return $this->__invoke($message, $type);
    }

    protected function callProxy(...$args)
    {
        $proxy = $this->proxy;

        if ($proxy instanceof Validator) {
            return $proxy->validate(...$args);
        }

        if (is_callable($proxy)) {
            return $proxy(...$args);
        }

        return null;
    }
}
