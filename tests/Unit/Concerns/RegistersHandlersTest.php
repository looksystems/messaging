<?php

namespace Tests\Concerns;

use Closure;
use Look\Messaging\Mock\MockHandler;
use Tests\Fixtures\TestHandler;

test('can register handler for message type', function () {

    $bus = $this->makeBus();
    $bus->handle('message.type', function ($message) {
        return $message;
    });

    $handlers = $bus->listOfHandlers();

    expect(count($handlers))->toEqual(1);
    expect($handlers[0])->toBeInstanceOf(Closure::class);

});

test('can register handler for multiple message types', function () {

    $bus = $this->makeBus();
    $bus->handle(
        [
            'message.type-1',
            'message.type-2',
        ],
        function ($message) {
            return $message;
        }
    );

    $handlers = $bus->listOfHandlers();

    expect(count($handlers))->toEqual(1);
    expect($handlers[0])->toBeInstanceOf(Closure::class);

});

test('can register list of handlers', function () {

    $bus = $this->makeBus();
    $bus->handle([
        'message.type-1' => MockHandler::class,
        'message.type-2' => TestHandler::class,
    ]);

    $handlers = $bus->listOfHandlers();

    expect(count($handlers))->toEqual(2);
    expect($handlers[0])->toEqual(MockHandler::class);
    expect($handlers[1])->toEqual(TestHandler::class);

});

test('can register mock handler', function () {

    $bus = $this->makeBus();
    $bus->mockHandler('message.type');

    $handlers = $bus->listOfHandlers();

    expect(count($handlers))->toEqual(1);
    expect($handlers[0])->toBeInstanceOf(MockHandler::class);

});

test('can list handlers', function () {

    $bus = $this->makeBus();

    $bus->mockHandler('message.type');
    $bus->handle('message.type', TestHandler::class);
    $bus->handle('message.type', function ($message) {
        return $message;
    });
    $bus->registerHandlers([
        'message.custom-1' => new TestHandler,
    ]);

    $handlers = $bus->listOfHandlers();

    expect(count($handlers))->toEqual(4);
    expect($handlers[0])->toBeInstanceOf(MockHandler::class);
    expect($handlers[1])->toEqual(TestHandler::class);
    expect($handlers[2])->toBeInstanceOf(Closure::class);
    expect($handlers[3])->toBeInstanceOf(TestHandler::class);

});
