<?php

namespace Tests\Unit\Middleware;

use Look\Messaging\Mock\MockHandler;
use Tests\Fixtures\Invokable;

test('can handle message via closure', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $called = false;
    $bus->handle(
        $type,
        function ($message) use (&$called) {
            $called = true;
        }
    );

    $bus->dispatch($message);

    expect($called)->toBeTrue();
    expect($bus->dispatched()->count())->toEqual(1);
    expect($bus->handled()->count())->toEqual(1);
    expect($bus->relayed()->count())->toEqual(0);

});

test('can handle message via invokable', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $invokable = new Invokable;
    $bus->handle($type, $invokable);

    $bus->dispatch($message);

    expect($invokable->message->getOriginal())->toEqual($message);
    expect($invokable->count)->toEqual(1);

});

test('can handle message with handler class', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $bus->handle($type, MockHandler::class);

    $bus->dispatch($message);

    expect($bus->handled()->first()->getOriginal())->toEqual($message);
    expect($bus->handled()->count())->toEqual(1);

});

test('can handle message with handler object', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $handler = new MockHandler;
    $bus->handle($type, $handler);

    $bus->dispatch($message);

    expect($handler->messages()->first()->getOriginal())->toEqual($message);
    expect($handler->messages()->count())->toEqual(1);

});

test('can dispatch message to multiple handlers', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $handler1 = $bus->mockHandler($type);
    $handler2 = $bus->mockHandler($type);

    $bus->dispatch($message);

    expect($handler1->messages()->first()->getOriginal())->toEqual($message);
    expect($handler1->messages()->count())->toEqual(1);

    expect($handler2->messages()->first()->getOriginal())->toEqual($message);
    expect($handler2->messages()->count())->toEqual(1);

});

test('fallback handler not called if message already handled', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $handler = $bus->mockHandler($type);
    $fallback = $bus->mockHandler($type, fallback: true);

    $bus->dispatch($message);

    expect($handler->messages()->first()->getOriginal())->toEqual($message);
    expect($handler->messages()->count())->toEqual(1);

    expect($fallback->messages()->count())->toEqual(0);

});

test('fallback handler called if message not handled', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $handler = $bus->mockHandler('not-'.$type);
    $fallback = $bus->mockHandler($type.':fallback');

    $bus->dispatch($message);

    expect($handler->messages()->count())->toEqual(0);

    expect($fallback->messages()->first()->getOriginal())->toEqual($message);
    expect($fallback->messages()->count())->toEqual(1);

});

test('will not call handler more than once', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $handler = $bus->mockHandler($type);
    $bus->handle('*', $handler);

    $bus->dispatch($message);

    expect($bus->handled()->count())->toEqual(1);
    expect($handler->messages()->first()->getOriginal())->toEqual($message);
    expect($handler->messages()->count())->toEqual(1);

});
