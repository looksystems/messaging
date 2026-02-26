<?php

namespace Look\Messaging\Slim;

use DI\Container;
use Look\Messaging\MessageBus as MessageBusInstance;
use Psr\Container\ContainerInterface;
use RuntimeException;

/*
 * @see \Look\Messaging\Contracts\MessageBus
 */
class MessageBus
{
    private static MessageBusInstance $instance;

    public static function init(
        ?ContainerInterface $container = null,
        array $config = []
    ): MessageBusInstance {
        self::$instance = new MessageBusInstance;

        if (!$container) {
            $container = new Container;
        }
        self::setContainer($container);

        if ($config) {
            self::$instance->applyConfig($config);
        }

        return self::$instance;
    }

    public static function setContainer(ContainerInterface $container): MessageBusInstance
    {
        if (!self::$instance) {
            throw new RuntimeException('Message bus not initialised');
        }

        self::$instance->setContainer($container);

        // register message bus singleton with container
        $container->set(MessageBusInstance::class, function () {
            return self::$instance;
        });

        return self::$instance;
    }

    public static function __callStatic(string $method, array $args)
    {
        if (!self::$instance) {
            throw new RuntimeException('Message bus not initialised');
        }

        return self::$instance->$method(...$args);
    }
}
