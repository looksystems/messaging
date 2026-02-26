<?php

namespace Tests\Concerns;

test('it can apply empty config', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $bus->applyConfig([]);

    $this->expect(true)->toBeTrue();

});

test('it can apply config', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $config = include $this->resources('config.php');

    $bus->applyConfig($config);

    $transformers = $bus->listOfTransformers();
    $transports = $bus->listOfTransports();
    $validators = $bus->listOfValidators();
    $handlers = $bus->listOfHandlers();
    $relays = $bus->listOfRelays();
    $transports = $bus->listOfTransports();

    expect(count($transformers))->toEqual(1);
    expect(count($validators))->toEqual(2);
    expect(count($handlers))->toEqual(1);
    expect(count($relays))->toEqual(1);
    expect(count($transports))->toEqual(3);

});
