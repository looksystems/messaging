<?php

namespace Look\Messaging\Decorators;

use Look\Messaging\Contracts\Decorator;

/**
 * @mixin StandardDecorator
 */
class DefaultDecorator
{
    private static Decorator|Closure|array|null $instance = null;

    public static function reset(): void
    {
        self::$instance = null;
    }

    public static function init(Decorator|Closure|array $decorator): void
    {
        self::$instance = $decorator;
    }

    public static function __callStatic(string $method, array $args)
    {
        return self::getInstance()->$method(...$args);
    }

    public static function getInstance(): Decorator
    {
        if (!self::$instance instanceof Decorator) {
            self::boot();
        }

        return self::$instance;
    }

    protected static function boot()
    {
        if (self::$instance instanceof Closure) {
            self::$instance = call_user_func(self::$instance);
        }

        if (is_array(self::$instance)) {
            self::$instance = StandardDecorator::make(self::$instance);
        }

        if (!self::$instance instanceof Decorator) {
            self::$instance = StandardDecorator::make();
        }
    }
}
