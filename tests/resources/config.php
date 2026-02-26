<?php

use Look\Messaging\Serializers\StandardSerializer;

return [
    'transformers' => [
        '*' => MyTransform::class,
    ],
    'schemas' => [
        '*' => [
            'id' => 'https://example.com/schema.json',
            'path' => '/path/to/schema.json',
        ],
    ],
    'validators' => [
        '*' => MyValidator::class,
    ],
    'handlers' => [
        '*' => MyHandler::class,
    ],
    'relays' => [
        '*' => MyRelay::class,
    ],
    'transports' => [
        'queue' => MyTransport::class,
    ],
    'serialize' => StandardSerializer::class,
    'decorate' => [
        'environment' => 'testing',
    ],
];
