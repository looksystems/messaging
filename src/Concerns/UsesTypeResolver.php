<?php

namespace Look\Messaging\Concerns;

use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\ProvidesMessage;
use Look\Messaging\Contracts\TypeResolver;

trait UsesTypeResolver
{
    protected TypeResolver $typeResolver;

    // TYPE RESOLVER

    public function resolve(object|array $message): ?MessageInterface
    {
        if ($message instanceof MessageInterface) {
            return $message;
        }

        if ($message instanceof ProvidesMessage) {
            return $message->toMessage();
        }

        $original = $message;
        if (is_array($message)) {
            $message = (object) $message;
        }

        $resolver = $this->getTypeResolver();
        if (!$resolver) {
            return null;
        }

        $resolved = $resolver->resolve($message);
        if ($resolved) {
            $resolved->setOriginal($original);
        }

        return $resolved;
    }

    public function setTypeResolver(TypeResolver $resolver): self
    {
        $this->typeResolver = $resolver;

        return $this;
    }

    public function getTypeResolver(): TypeResolver
    {
        return $this->typeResolver;
    }
}
