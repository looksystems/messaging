<?php

namespace Look\Messaging\Support;

class Wildcard
{
    public static function findByKey(?string $needle, array $haystack, string $delimiter = '.', string $wildcard = '*'): array
    {
        if (is_null($needle) || $needle === '') {
            return [];
        }

        $found = [];
        $parts = explode($delimiter, $needle);
        while (true) {
            if (isset($haystack[$needle])) {
                if (is_array($haystack[$needle])) {
                    $found = array_merge($found, $haystack[$needle]);
                } else {
                    $found[] = $haystack[$needle];
                }
            }

            if ($needle === $wildcard) {
                break;
            }

            array_pop($parts);

            $needle = implode($delimiter, array_merge($parts, [$wildcard]));
        }

        return $found;
    }

    public static function firstByKey(?string $needle, array $haystack, string $delimiter = '.', string $wildcard = '*'): mixed
    {
        $matches = self::findByKey($needle, $haystack, $delimiter, $wildcard);

        return $matches[0] ?? null;
    }
}
