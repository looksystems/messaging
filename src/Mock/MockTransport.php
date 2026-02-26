<?php

namespace Look\Messaging\Mock;

use Exception;
use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\Transport;
use Look\Messaging\Support\MessageHistory;
use Look\Messaging\Transports\Concerns\WithDecorationMethods;
use Look\Messaging\Transports\Concerns\WithSerializationMethods;

class MockTransport implements Transport
{
    use WithDecorationMethods;
    use WithSerializationMethods;

    protected string $defaultChannel = 'default';

    protected array $buffer = [];
    protected array $queue = [];
    protected array $sent = [];

    // INSTANTIATION

    public function __construct(?string $defaultChannel = null)
    {
        if (isset($defaultChannel)) {
            $this->setDefaultChannel($defaultChannel);
        }
    }

    // TRANSPORT

    public function send(MessageInterface $message, ?array $args = null): ?bool
    {
        $channels = $args ?? [$this->defaultChannel];
        $channels = array_unique(array_filter($channels));

        $relayed = false;
        foreach ($channels as $channel) {
            if (!isset($this->sent[$channel])) {
                $this->sent[$channel] = new MessageHistory;
            }
            $this->sent[$channel]->push($message);
            $relayed = true;
        }

        return $relayed;
    }

    public function receive(?array $args = null): array
    {
        $channels = $args ?? [$this->defaultChannel];
        $channels = array_unique(array_filter($channels));

        $messages = [];
        foreach ($channels as $channel) {
            $pulled = $this->buffer[$channel] ?? [];
            unset($this->buffer[$channel]);

            $messages = array_merge($messages, $pulled);

            $this->queue[$channel] = true;
        }

        return $messages;
    }

    // channel

    public function push(array|object $messageOrList, ?string $channel = null): self
    {
        if (!isset($channel)) {
            $channel = $this->defaultChannel;
            if (!$channel) {
                throw new Exception('No default channel');
            }
        }

        if (!isset($this->buffer[$channel])) {
            $this->buffer[$channel] = [];
        }

        if (is_array($messageOrList)) {
            $this->buffer[$channel] = array_merge($this->buffer[$channel], $messageOrList);
        } elseif (is_object($messageOrList)) {
            $this->buffer[$channel][] = $messageOrList;
        }

        return $this;
    }

    public function setDefaultChannel(string $channel): self
    {
        $this->defaultChannel = $channel;

        return $this;
    }

    // HISTORY

    public function sent(?string $channel = null): bool
    {
        if (is_null($channel)) {
            foreach ($this->sent as $history) {
                if ($history->count()) {
                    return true;
                }
            }

            return false;
        }

        if (isset($this->sent[$channel])) {
            return (bool) $this->sent[$channel]->count();
        }

        return false;
    }

    public function queued(?string $channel = null): bool
    {
        if (is_null($channel)) {
            return (bool) count($this->queue);
        }

        if (isset($this->queue[$channel])) {
            return (bool) $this->queue[$channel];
        }

        return false;
    }

    public function messages(?string $channel): MessageHistory
    {
        return $this->sent[$channel] ?? new MessageHistory;
    }
}
