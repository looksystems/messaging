<?php

namespace Look\Messaging\Middleware;

use Look\Messaging\Contracts\Handler;
use Look\Messaging\Support\DedupeSession;

class HandleMessage extends Middleware
{
    public function handle($message)
    {
        if (!$this->state->canHandle()) {
            return $this->next($message);
        }

        $type = $message->type();
        if (!$type) {
            return $this->next($message);
        }

        $this->call(
            $this->bus->listOfHandlers($type, fallback: false),
            $type,
            $message
        );

        if (
            !$this->state->wasHandled()
            && !$this->state->wasRelayed()
        ) {
            $this->call(
                $this->bus->listOfHandlers($type, fallback: true),
                $type,
                $message,
                true
            );
        }

        if ($this->state->wasHandled()) {
            $this->bus->handled()->push($message);
        }

        return $this->next($message);
    }

    protected function call(array $handlers, string $type, object $message, bool $stopWhenHandled = false): void
    {
        $dedupe = new DedupeSession;
        foreach ($handlers as $handler) {
            if (!$this->state->canHandle() || $this->state->wasStopped()) {
                break;
            }

            if ($dedupe->called($handler)) {
                continue;
            }

            if (is_string($handler)) {
                $handler = $this->container()->get($handler);
            }

            $context = [
                'type' => $type,
                'message' => $message,
                'state' => $this->state,
                'bus' => $this->bus,
            ];

            if ($handler instanceof Handler) {
                $response = $this->container()->call([$handler, 'handle'], $context);
            } elseif (is_callable($handler)) {
                $response = $this->container()->call($handler, $context);
            } else {
                continue;
            }

            if ($response ?? true) {
                $this->state->markAsHandled();
                if ($stopWhenHandled) {
                    break;
                }
            }
        }
    }
}
