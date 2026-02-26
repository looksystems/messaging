<?php

namespace Look\Messaging\Support;

class MessageHistory
{
    protected array $history = [];

    public function push(object $message): self
    {
        $this->history[] = $message;

        return $this;
    }

    public function first()
    {
        return current($this->history);
    }

    public function last()
    {
        return end($this->history);
    }

    public function all(): array
    {
        return $this->history;
    }

    public function contains($match): bool
    {
        foreach ($this->history as $message) {
            // TODO: need to get type from message
            if (is_string($match)) {
                if ($match === get_class($message)) {
                    return true;
                }
            } elseif (is_object($match)) {
                if ($match === $message) {
                    return true;
                }
            }
        }

        return false;
    }

    public function count(): int
    {
        return count($this->history);
    }

    public function clear(): self
    {
        $this->history = [];

        return $this;
    }
}
