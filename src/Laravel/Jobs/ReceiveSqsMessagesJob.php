<?php

namespace Look\Messaging\Laravel\Jobs;

use Exception;
use Look\Messaging\Contracts\MessageBus;
use Look\Messaging\Transports\SqsTransport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ReceiveSqsMessagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    protected $queues = [];

    public function __construct(array $queues = [])
    {
        if ($queues) {
            $this->addQueues($queues);
        }
    }

    public function addQueues(array $queues = [])
    {
        $this->queues = array_merge($this->queues, $queues);

        return $this;
    }

    public function handle(MessageBus $bus)
    {
        if (empty($this->queues)) {
            return;
        }

        $messages = (new SqsTransport)->receive($this->queues);

        foreach ($messages as $message) {
            try {
                $bus->dispatch($message); // FIXME: canRelay: false
            } catch (Exception $e) {
                // fallback to laravel queue worker for better retry/exception handling
                QueuedMessageJob::dispatch($message);
            }
        }
    }
}
