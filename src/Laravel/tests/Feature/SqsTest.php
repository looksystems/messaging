<?php

namespace Tests\Feature;

use Look\Messaging\Laravel\Facades\MessageBus;
use Tests\TestCase;

class SqsTest extends TestCase
{
    public function test_can_send_message_via_sqs(): void
    {
        if (!env('MESSAGING_DEFAULT_SQS_QUEUES')) {
            $this->markTestSkipped('default sqs queue not defined in phpunit.xml');
            return;
        }

        MessageBus::relay('*', 'sqs');

        MessageBus::dispatch((object) [
            '_type' => 'test',
            'test' => true,
        ]);

        $this->assertEquals(1, MessageBus::relayed()->count());
    }
}
