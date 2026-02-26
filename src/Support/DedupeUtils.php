<?php

namespace Look\Messaging\Support;

class DedupeUtils
{
    public static function list(array $list): array
    {
        $unique = [];
        foreach ($list as $callable) {
            $key = self::key($callable);
            if ($key) {
                $unique[$key] = $callable;
            } else {
                $unique[] = $callable;
            }
        }

        return array_values($unique);
    }

    public static function key($callable): ?string
    {
        if (is_string($callable)) {
            return $callable;
        } elseif (is_object($callable)) {
            return spl_object_id($callable);
        }

        return @json_encode($callable);
    }
}
