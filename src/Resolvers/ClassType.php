<?php

namespace Look\Messaging\Resolvers;

use Look\Messaging\Support\Wildcard;

class ClassType extends AbstractResolver
{
    public function type(object $message): ?string
    {
        return get_class($message);
    }

    public function match(string $type, array $list): array
    {
        return Wildcard::findByKey($type, $list, '\\', '');
    }
}
