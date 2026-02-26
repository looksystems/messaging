<?php

namespace Look\Messaging\Transports\Concerns;

use Closure;
use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\Serializer;
use Look\Messaging\Serializers\DefaultSerializer;

trait WithSerializationMethods
{
    protected Serializer|Closure|null $serializeUsing = null;
    protected Serializer|Closure|null $unserializeUsing = null;

    public function serialize(MessageInterface $message): mixed
    {
        if ($this->serializeUsing) {
            if ($this->serializeUsing instanceof Serializer) {
                return $this->serializeUsing->serialize($message);
            }

            return call_user_func($this->serializeUsing, $message);
        }

        return DefaultSerializer::serialize($message);
    }

    public function serializeUsing(Serializer|Closure $callback): self
    {
        $this->serializeUsing = $callback;

        return $this;
    }

    public function unserialize(mixed $data): MessageInterface
    {
        if ($this->unserializeUsing) {
            if ($this->unserializeUsing instanceof Serializer) {
                return $this->unserializeUsing->unserialize($data);
            }

            return call_user_func($this->unserializeUsing, $data);
        }

        return DefaultSerializer::unserialize($data);
    }

    public function unserializeUsing(Serializer|Closure $callback): self
    {
        $this->unserializeUsing = $callback;

        return $this;
    }
}
