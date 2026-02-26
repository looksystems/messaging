<?php

namespace Look\Messaging\Serializers;

use Closure;
use Look\Messaging\Contracts\Serializer;

class DefaultSerializer
{
    private static Serializer|Closure|null $instance = null;

    public static function reset(): void
    {
        self::$instance = null;
    }

    public static function init(Serializer|Closure $serializer): void
    {
        self::$instance = $serializer;
    }

    public static function __callStatic(string $method, array $args)
    {
        return self::getInstance()->$method(...$args);
    }

    public static function getInstance(): Serializer
    {
        if (!self::$instance instanceof Serializer) {
            self::boot();
        }

        return self::$instance;
    }

    protected static function boot()
    {
        if (self::$instance instanceof Closure) {
            self::$instance = call_user_func(self::$instance);
        }

        if (!self::$instance instanceof Serializer) {
            self::$instance = StandardSerializer::make();
        }
    }
}
