<?php

namespace Look\Messaging\Support;

use Psr\Log\LoggerInterface;

class Log
{
    protected static ?LoggerInterface $logger = null;

    public static function __callStatic($method, $args)
    {
        return self::$logger?->$method(...$args);
    }

    public static function setLogger(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public static function instance(): ?LoggerInterface
    {
        return self::$logger;
    }
}
