<?php

namespace Tests\Concerns;

use Look\Messaging\Contracts\Transport;
use Look\Messaging\Mock\MockTransport;
use Look\Messaging\Transports\SnsTransport;
use Look\Messaging\Transports\SqsTransport;

test('sqs transport registered by default', function () {

    $bus = $this->makeBus();

    $transport = $bus->transport('sqs');

    expect($transport)->toBeInstanceOf(SqsTransport::class);

});

test('sns transport registered by default', function () {

    $bus = $this->makeBus();

    $transport = $bus->transport('sns');

    expect($transport)->toBeInstanceOf(SnsTransport::class);

});

test('can register named transport as string', function () {

    $bus = $this->makeBus();
    $bus->registerTransport('custom', 'sqs');

    $sqs = $bus->transport('sqs');
    $transport = $bus->transport('custom');

    expect($transport)->toBeInstanceOf(SqsTransport::class);
    expect($transport)->toEqual($sqs);
});

test('can register named transport as string with args', function () {

    $bus = $this->makeBus();
    $bus->registerTransport('custom', 'sqs:queue');

    $transport = $bus->transport('custom');

    expect($transport)->toBeInstanceOf(Transport::class);
    expect($transport->getDefaultQueues())->toEqual(['queue']);

});

test('can register named sqs transport as array', function () {

    $bus = $this->makeBus();
    $bus->registerTransport('custom', ['type' => 'sqs', 'queues' => 'queue']);

    $transport = $bus->transport('custom');

    expect($transport)->toBeInstanceOf(Transport::class);
    expect($transport->getDefaultQueues())->toEqual(['queue']);

});

test('can register named sns transport as array', function () {

    $bus = $this->makeBus();
    $bus->registerTransport('custom', ['type' => 'sns', 'topics' => 'topic']);

    $transport = $bus->transport('custom');

    expect($transport)->toBeInstanceOf(Transport::class);
    expect($transport->getDefaultTopics())->toEqual(['topic']);

});

test('can register named transport as closure', function () {

    $bus = $this->makeBus();
    $bus->registerTransport('custom', function () {
        return new SqsTransport;
    });

    $transport = $bus->transport('custom');

    expect($transport)->toBeInstanceOf(SqsTransport::class);

});

test('can register named transport as class', function () {

    $bus = $this->makeBus();
    $bus->registerTransport('custom', SqsTransport::class);

    $transport = $bus->transport('custom');

    expect($transport)->toBeInstanceOf(SqsTransport::class);

});

test('can register mock transport', function () {

    $bus = $this->makeBus();
    $bus->mockTransport('mock');

    $transport = $bus->transport('mock');

    expect($transport)->toBeInstanceOf(MockTransport::class);

});

test('can list transports', function () {

    $bus = $this->makeBus();

    $bus->registerTransport('custom-sqs', 'sqs');
    $bus->registerTransport('custom-sns', 'sns');
    $bus->mockTransport('mock');

    expect(
        array_keys($bus->listOfTransports())
    )->toEqual(
        [
            'sqs',
            'sns',
            'custom-sqs',
            'custom-sns',
            'mock',
        ]
    );

});

test('array transport without type resolves to null', function () {

    $bus = $this->makeBus();
    $bus->registerTransport('custom', ['queues' => 'queue']);

    $transport = $bus->transport('custom');

    expect($transport)->toBeNull();

});

test('array transport with unknown type resolves to null', function () {

    $bus = $this->makeBus();
    $bus->registerTransport('custom', ['type' => 'unknown', 'queues' => 'queue']);

    $transport = $bus->transport('custom');

    expect($transport)->toBeNull();

});

test('invalid transport resolves to null', function () {

    $bus = $this->makeBus();
    $bus->registerTransport('custom', function () {
        return new \StdClass;
    });

    $transport = $bus->transport('custom');

    expect($transport)->toBeNull();

});

test('transport with configured decorator will decorate messages correctly', function () {

    $bus = $this->makeBus();
    $bus->applyConfig([
        'transports' => [
            'custom' => [
                'type' => 'sqs',
                'decorate' => [
                    'environment' => 'testing',
                ],
            ],
        ],
    ]);

    $transport = $bus->transport('custom');
    $data = (object) [];
    $decorated = $transport->decorate($data);

    expect($decorated)->toEqual((object) ['environment' => 'testing']);

});
