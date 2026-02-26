<?php

namespace Look\Messaging\Handlers;

use Closure;
use Look\Messaging\Contracts\Handler;
use Look\Messaging\Contracts\MessageBus;
use Look\Messaging\Contracts\MessageInterface;

class Dedupe implements Handler
{
    protected static array $shutdownCallbacks = [];

    protected array $queue = [];

    protected Closure|string|null $identifyMessageUsing = null;
    protected ?Closure $handleDuplicatesUsing = null;

    protected bool $dispatchFirst = true;
    protected bool $shutdownFunctionRegistered = false;
    protected bool $dispatchingQueue = false;

    // INSTANTIATION

    public static function make(): self
    {
        return new self;
    }

    public static function usingFirst(Closure|string|null $identifyMessageUsing = null): self
    {
        $handler = (new self)->dispatchFirst();
        if ($identifyMessageUsing) {
            $handler->identifyMessageUsing($identifyMessageUsing);
        }

        return $handler;
    }

    public static function usingLast(Closure|string|null $identifyMessageUsing = null): self
    {
        $handler = (new self)->dispatchLast();
        if ($identifyMessageUsing) {
            $handler->identifyMessageUsing($identifyMessageUsing);
        }

        return $handler;
    }

    // MESSAGE ID

    public function identifyMessageUsing(Closure|string $identifyMessageUsing): self
    {
        $this->identifyMessageUsing = $identifyMessageUsing;

        return $this;
    }

    protected function resolveMessageIdentity(object $message)
    {
        $identifyMessageUsing = $this->identifyMessageUsing;
        if (!$identifyMessageUsing) {
            return null;
        }

        if ($identifyMessageUsing instanceof Closure) {
            return $identifyMessageUsing($message);
        }

        return $message->$identifyMessageUsing ?? null;
    }

    protected function generateMessageHash(MessageInterface $message)
    {
        if ($message->getOriginal()) {
            $payload = $message->getOriginal();
        } else {
            $payload = $message->payload();
        }

        if (is_object($payload)) {
            return spl_object_id($payload);
        }

        return md5(json_encode($payload));
    }

    // DUPLICATES

    public function handleDuplicatesUsing(Closure $callback): self
    {
        $this->handleDuplicatesUsing = $callback;

        return $this;
    }

    protected function handleDuplicate(MessageInterface $message)
    {
        if ($this->handleDuplicatesUsing) {
            call_user_func($this->handleDuplicatesUsing, $message);
        }
    }

    // DISPATCH

    public function dispatchFirst(): self
    {
        $this->dispatchFirst = true;

        return $this;
    }

    public function dispatchLast(): self
    {
        $this->dispatchFirst = false;

        return $this;
    }

    // HANDLER

    public function handle($message, $state, $bus)
    {
        // skip if we're dispatching the queue
        if ($this->dispatchingQueue) {
            // fyi: returning false means message won't be marked as "handled"
            return false;
        }

        // find key used to identify duplicates
        $id = $this->resolveMessageIdentity($message);
        if (!$id) {
            $id = $this->generateMessageHash($message);
        }

        $seen = $this->queue[$id] ?? null;

        $this->queue[$id] = $message;

        if ($this->dispatchFirst) {
            if (!$seen) {
                // allow message to continue to be processed as usual
                return false;
            }
            $this->handleDuplicate($message);
        } else {
            if (!$this->shutdownFunctionRegistered) {
                $this->registerShutdownFunction($bus);
            }
            if ($seen) {
                $this->handleDuplicate($seen);
            }
        }

        // prevent any further processing of this message
        $state->abort();

        // fyi: returning false means message won't be marked as "handled"
        return false;
    }

    protected function registerShutdownFunction(MessageBus $bus): void
    {
        if (empty(self::$shutdownCallbacks)) {
            register_shutdown_function(function () {
                Dedupe::flush();
            });
        }

        self::$shutdownCallbacks[] = function () use ($bus) {
            $this->dispatchQueue($bus);
        };

        $this->shutdownFunctionRegistered = true;
    }

    /**
     * @internal
     */
    public function dispatchQueue(MessageBus $bus): self
    {
        $this->dispatchingQueue = true;
        foreach ($this->queue as $message) {
            $bus->dispatch($message);
        }
        $this->dispatchingQueue = false;
        $this->queue = [];

        return $this;
    }

    public static function flush(): void
    {
        foreach (self::$shutdownCallbacks as $callback) {
            $callback();
        }
    }
}
