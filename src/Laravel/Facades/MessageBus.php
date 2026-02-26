<?php

namespace Look\Messaging\Laravel\Facades;

use Look\Messaging\Envelope;
use Look\Messaging\Laravel\FakeMessageBus;
use Look\Messaging\Laravel\MessageBus as BaseMessageBus;
use Illuminate\Support\Facades\Facade;

/**
 * @method BaseMessageBus dispatch(object|array|string $messageOrType, object|array $payload = [], Envelope|array $envelope = [], bool $throwIfInvalid = true)
 * @method void assertDispatched(string $type, ?array $payload = null)
 * @method void assertNotDispatched(string $type, ?array $payload = null)
 *
 * @see BaseMessageBus
 * @see FakeMessageBus
 */
class MessageBus extends Facade
{
    public static function fake(
        array $relays = [],
        array $transports = [],
    ): FakeMessageBus {
        /** @var BaseMessageBus $actualMessageBus */
        $actualMessageBus = static::isFake()
            ? static::getFacadeRoot()->messageBus
            : static::getFacadeRoot();

        $fake = new FakeMessageBus(
            messageBus: $actualMessageBus,
            relays: $relays,
            transports: $transports
        );

        return tap($fake, function ($fake) {
            static::swap($fake);
        });
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'look.message-bus';
    }
}
