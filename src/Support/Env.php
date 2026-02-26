<?php

namespace Look\Messaging\Support;

class Env
{
    public static function get(string $value, $default = null)
    {
        return $_ENV[$value] ?? getenv($value) ?? $default;
    }
}
