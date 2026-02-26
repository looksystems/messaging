<?php

namespace Look\Messaging\Support;

class Str
{
    public static function studly(string $value): string
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $value));
        $studlyWords = array_map(fn ($word) => ucfirst($word), $words);

        return implode($studlyWords);
    }

    public static function nameAndArgs(string $value): array
    {
        $parts = explode(':', $value, 2);

        $name = $parts[0];

        if (!isset($parts[1]) || $parts[1] === '') {
            $args = null;
        } else {
            $args = explode(',', $parts[1]);
            array_walk($args, 'trim');
        }

        return [$name, $args];
    }
}
