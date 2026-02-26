<?php

namespace Tests\Feature;

use Look\Messaging\Exceptions\InvalidMessageException;
use Look\Messaging\Laravel\Facades\MessageBus;
use Tests\TestCase;

class RulesTest extends TestCase
{
    public function test_throws_exception_with_invalid_message(): void
    {
        $this->expectException(InvalidMessageException::class);

        MessageBus::rules('*', ['required' => 'required']);

        MessageBus::dispatch((object) [
            '_type' => 'test',
        ]);
    }

    public function test_dispatches_valid_message(): void
    {
        MessageBus::rules('*', ['required' => 'required']);

        MessageBus::dispatch((object) [
            '_type' => 'test',
            'required' => true,
        ]);

        $this->assertEquals(1, MessageBus::dispatched()->count());
    }
}
