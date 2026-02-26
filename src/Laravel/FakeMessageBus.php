<?php

namespace Look\Messaging\Laravel;

use Look\Messaging\Laravel\Concerns\Assertions;
use Illuminate\Support\Testing\Fakes\Fake;

class FakeMessageBus implements Fake
{
    use Assertions;

    public function __construct(
        protected MessageBus $messageBus,
        protected array $relays,
        protected array $transports,
    ) {
        $this->mockTransports();
    }

    private function mockTransports(): void
    {
        collect($this->messageBus->listOfTransports())
            ->each(fn ($_, $name) => $this->messageBus->mockTransport($name));
    }

    public function __call(string $method, array $parameters)
    {
        return $this->messageBus->{$method}(...$parameters);
    }
}
