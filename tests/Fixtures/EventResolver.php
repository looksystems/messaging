<?php

namespace Tests\Fixtures;

use Look\Messaging\Resolvers\MessageProperty;

class EventResolver extends MessageProperty
{
    public function type(object $message): ?string
    {
        if (isset($message->entity_type) && $message->event_type) {
            return 'shop.events.'.$message->entity_type.'-'.$message->event_type;
        }

        return null;
    }
}
