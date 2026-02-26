<?php

namespace Tests;

use DI\Container;
use Look\Messaging\Decorators\DefaultDecorator;
use Look\Messaging\MessageBus;
use Look\Messaging\Serializers\DefaultSerializer;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\TestMessage;

class MessageBusTestCase extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        DefaultSerializer::reset();
        DefaultDecorator::reset();
    }

    protected function makeBus(): MessageBus
    {
        return new MessageBus(new Container);
    }

    protected function makeBusAndMessage(): array
    {
        $bus = $this->makeBus();
        $message = new TestMessage;
        $type = $bus->getTypeResolver()->type($message);

        return [$bus, $message, $type];
    }

    protected function resources(?string $path = null): string
    {
        return $this->testpath('resources/'.$path);
    }

    protected function testpath(?string $path = null): string
    {
        return __DIR__.'/'.$path;
    }
}
