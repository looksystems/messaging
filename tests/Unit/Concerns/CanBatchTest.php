<?php

namespace Tests\Concerns;

test('batch messages are not dispatched', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $handler = $bus->mockHandler('*');

    $bus->batch();
    $bus->dispatch($message);
    expect($bus->dispatched()->count())->toEqual(0);
    expect($handler->called())->toEqual(0);
    expect($bus->pending()->count())->toEqual(1);

});

test('it can release batched messages', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $handler = $bus->mockHandler('*');

    $bus->batch();
    $bus->dispatch($message);
    $bus->release();

    expect($bus->dispatched()->count())->toEqual(1);
    expect($handler->called())->toEqual(1);
    expect($bus->pending()->count())->toEqual(0);

});

test('it ignores release when batch not open', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $handler = $bus->mockHandler('*');

    $bus->release();

    $bus->batch();
    $bus->dispatch($message);

    $bus->release();
    $bus->release();

    expect($bus->dispatched()->count())->toEqual(1);
    expect($handler->called())->toEqual(1);
    expect($bus->pending()->count())->toEqual(0);

});

test('it can drop batched messages', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $handler = $bus->mockHandler('*');

    $bus->batch();
    $bus->dispatch($message);
    $bus->drop();

    expect($bus->dispatched()->count())->toEqual(0);
    expect($handler->called())->toEqual(0);
    expect($bus->pending()->count())->toEqual(0);

});

test('nested batches will only be dispatched with last release', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $handler = $bus->mockHandler('*');

    $bus->batch();
    $bus->batch();
    $bus->dispatch($message);

    $bus->release();

    expect($bus->dispatched()->count())->toEqual(0);
    expect($handler->called())->toEqual(0);
    expect($bus->pending()->count())->toEqual(1);

    $bus->release();

    expect($bus->dispatched()->count())->toEqual(1);
    expect($handler->called())->toEqual(1);
    expect($bus->pending()->count())->toEqual(0);

});

test('nested batches will be dropped even if least operation is release', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $handler = $bus->mockHandler('*');

    $bus->batch();
    $bus->batch();
    $bus->dispatch($message);

    $bus->drop();

    expect($bus->dispatched()->count())->toEqual(0);
    expect($handler->called())->toEqual(0);
    expect($bus->pending()->count())->toEqual(0);

    $bus->dispatch($message);
    $bus->release();

    expect($bus->dispatched()->count())->toEqual(0);
    expect($handler->called())->toEqual(0);
    expect($bus->pending()->count())->toEqual(0);

});

test('closure will auto release batched messages', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $handler = $bus->mockHandler('*');
    $bus->batch(function ($bus) use ($message) {
        $bus->dispatch($message);
    });

    expect($bus->dispatched()->count())->toEqual(1);
    expect($handler->called())->toEqual(1);
    expect($bus->pending()->count())->toEqual(0);

});

test('closure will auto drop batched messages when exception is thrown', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $handler = $bus->mockHandler('*');

    try {
        $bus->batch(function ($bus) use ($message) {
            $bus->dispatch($message);
            throw new \Exception;
        });
    } catch (\Exception $e) {
    }

    expect($bus->dispatched()->count())->toEqual(0);
    expect($handler->called())->toEqual(0);
    expect($bus->pending()->count())->toEqual(0);

});
