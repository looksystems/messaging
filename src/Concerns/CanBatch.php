<?php

namespace Look\Messaging\Concerns;

use Closure;
use Look\Messaging\Exceptions\InvalidMessageException;
use Look\Messaging\Support\ListOfMessages;
use Throwable;

trait CanBatch
{
    protected int $batchLevel = 0;
    protected bool $batchDropped = false;
    protected array $pending = [];

    public function batch(?Closure $callback = null): self
    {
        $this->batchLevel++;

        if ($callback) {
            try {
                $this->getContainer()->call($callback, ['bus' => $this]);
                $this->release();
            } catch (Throwable $e) {
                $this->drop();
                throw $e;
            }
        }

        return $this;
    }

    /**
     * @throws InvalidMessageException
     */
    public function release(): self
    {
        if (!$this->batchLevel) {
            return $this;
        }

        $this->batchLevel--;

        if (!$this->batchLevel) {
            if (!$this->batchDropped) {
                while ($this->pending) {
                    $message = array_shift($this->pending);
                    $this->dispatch($message);
                }
            }
            $this->pending = [];
            $this->batchDropped = false;
        }

        return $this;
    }

    public function drop(): self
    {
        $this->batchDropped = true;
        $this->release();

        return $this;
    }

    public function pending(): ListOfMessages
    {
        return new ListOfMessages($this, $this->batchDropped ? [] : $this->pending);
    }
}
