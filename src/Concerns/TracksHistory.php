<?php

namespace Look\Messaging\Concerns;

use Look\Messaging\Support\MessageHistory;

trait TracksHistory
{
    protected MessageHistory $dispatched;
    protected MessageHistory $relayed;
    protected MessageHistory $handled;

    // HISTORY

    public function dispatched(): MessageHistory
    {
        if (!isset($this->dispatched)) {
            $this->dispatched = new MessageHistory;
        }

        return $this->dispatched;
    }

    public function relayed(): MessageHistory
    {
        if (!isset($this->relayed)) {
            $this->relayed = new MessageHistory;
        }

        return $this->relayed;
    }

    public function handled(): MessageHistory
    {
        if (!isset($this->handled)) {
            $this->handled = new MessageHistory;
        }

        return $this->handled;
    }
}
