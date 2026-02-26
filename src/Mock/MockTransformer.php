<?php

namespace Look\Messaging\Mock;

use Look\Messaging\Contracts\Transformer;

class MockTransformer extends MockInvokable implements Transformer
{
    public function transform(object $message): object
    {
        return $this->__invoke($message);
    }

    public function __invoke(...$args)
    {
        if (isset($args[0])) {
            $this->history->push($args[0]);
        }

        if ($this->proxy) {
            return $this->callProxy(...$args);
        }

        return $args[0] ?? (object) [];
    }

    protected function callProxy(...$args)
    {
        $proxy = $this->proxy;
        if ($proxy instanceof Transformer) {
            return $proxy->transform(...$args);
        }
        if (is_callable($proxy)) {
            return $proxy(...$args);
        }

        return $args[0] ?? (object) [];
    }
}
