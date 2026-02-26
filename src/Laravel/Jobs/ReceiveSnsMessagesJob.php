<?php

namespace Look\Messaging\Laravel\Jobs;

use Exception;
use Look\Messaging\Contracts\MessageBus;
use Look\Messaging\Transports\SnsTransport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ReceiveSnsMessagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    protected $topics = [];

    public function __construct(array $topics = [])
    {
        if ($topics) {
            $this->addTopics($topics);
        }
    }

    public function addTopics(array $topics = [])
    {
        $this->topics = array_merge($this->topics, $topics);

        return $this;
    }

    public function handle(MessageBus $bus)
    {
        if (empty($this->queues)) {
            return;
        }

        $messages = (new SnsTransport)->receive($this->topics);

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
