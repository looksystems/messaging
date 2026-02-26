<?php

namespace Look\Messaging\Concerns;

use Closure;

trait CanBoot
{
    protected array $deferred = [];

    // BOOT

    public function booting(Closure $callback): self
    {
        $this->deferred[] = $callback;

        return $this;
    }

    protected function boot(): void
    {
        if (!$this->deferred) {
            return;
        }

        foreach ($this->deferred as $callback) {
            $callback($this);
        }

        $this->deferred = [];
    }
}
