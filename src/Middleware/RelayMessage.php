<?php

namespace Look\Messaging\Middleware;

use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\Relay;
use Look\Messaging\Support\DedupeSession;
use Look\Messaging\Support\Str;

class RelayMessage extends Middleware
{
    public function handle($message)
    {
        if (!$this->state->canRelay()) {
            return $this->next($message);
        }

        $type = $message?->type();
        if (!$type) {
            return $this->next($message);
        }

        $this->send(
            $this->bus->listOfRelays($type, fallback: false),
            $type,
            $message
        );

        if (
            !$this->state->wasRelayed()
            && !$this->state->wasHandled()
        ) {
            $this->send(
                $this->bus->listOfRelays($type, fallback: true),
                $type,
                $message,
                true
            );
        }

        if ($this->state->wasRelayed()) {
            $this->bus->relayed()->push($message);
        }

        return $this->next($message);
    }

    protected function send(array $relays, string $type, MessageInterface $message, bool $stopWhenRelayed = false): void
    {
        $dedupe = new DedupeSession;
        foreach ($relays as $relay) {
            if (!$this->state->canRelay() || $this->state->wasStopped()) {
                break;
            }

            if ($dedupe->called($relay)) {
                continue;
            }

            if (is_string($relay)) {
                if (class_exists($relay)) {
                    $relay = $this->container()->get($relay);
                } else {
                    $response = $this->sendVia($relay, $message);
                    if ($response ?? true) {
                        $this->state->markAsRelayed();
                        if ($stopWhenRelayed) {
                            break;
                        }
                    }
                    continue;
                }
            }

            $context = [
                'type' => $type,
                'message' => $message,
                'state' => $this->state,
                'bus' => $this->bus,
            ];

            if ($relay instanceof Relay) {
                $response = $this->container()->call([$relay, 'relay'], $context);
            } elseif (is_callable($relay)) {
                $response = $this->container()->call($relay, $context);
            } else {
                continue;
            }

            if ($response ?? true) {
                $this->state->markAsRelayed();
                if ($stopWhenRelayed) {
                    break;
                }
            }
        }
    }

    protected function sendVia(string $transport, MessageInterface $message): ?bool
    {
        [$transportName, $transportArgs] = Str::nameAndArgs($transport);
        if (!$transportName) {
            return false;
        }

        $transport = $this->bus->transport($transportName);
        if (!$transport) {
            return false;
        }

        return $transport->send($message, $transportArgs);
    }
}
