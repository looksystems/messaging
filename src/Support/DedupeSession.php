<?php

namespace Look\Messaging\Support;

class DedupeSession
{
    protected array $called = [];

    public function called($callable): bool
    {
        $key = DedupeUtils::key($callable);

        if (empty($key)) {
            return false;
        }

        if (!empty($this->called[$key])) {
            return true;
        }

        $this->called[$key] = true;

        return false;
    }
}
