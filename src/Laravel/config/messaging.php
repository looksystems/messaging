<?php

use Look\Messaging\Laravel\QueueTransport;

return [
    'transformers' => [
        //
    ],
    'rules' => [
        //
    ],
    'schemas' => [
        //
    ],
    'validators' => [
        //
    ],
    'handlers' => [
        //
    ],
    'relays' => [
        //
    ],
    'transports' => [
        'sqs' => [
            'type' => 'sqs',
            'queues' => env('MESSAGING_DEFAULT_SQS_QUEUES'),
        ],
        'sns' => [
            'type' => 'sns',
            'topics' => env('MESSAGING_DEFAULT_SNS_TOPICS'),
        ],
        'queue' => QueueTransport::class,
    ],
    'decorate' => [
        // 'environment' => env('MESSAGING_ENVIRONMENT'),
    ],
];
