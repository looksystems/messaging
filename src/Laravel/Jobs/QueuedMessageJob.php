<?php

namespace Look\Messaging\Laravel\Jobs;

use Look\Messaging\Contracts\MessageBus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class QueuedMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function handle(MessageBus $bus)
    {
        // fyi: exception handling managed by queue worker
        $bus->dispatch($this->message); // FIXME: canRelay: false
    }
}
