<?php

namespace Look\Messaging\Laravel;

use Look\Messaging\Contracts\Transport;
use Look\Messaging\Laravel\Jobs\QueuedMessageJob;

class QueueTransport implements Transport
{
    // MESSAGE BUS RELAY

    public function send($message, ?array $args = null): ?bool
    {
        if ($args) {
            foreach ($args as $queue) {
                $job = QueuedMessageJob::dispatch($message);
                if ($queue) {
                    $job->onQueue($queue);
                }
            }
        } else {
            QueuedMessageJob::dispatch($message);
        }

        return true;
    }

    public function receive(?array $args = null): array
    {
        // do nothing: handled by laravel queue worker
        return [];
    }
}
