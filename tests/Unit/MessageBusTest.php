<?php

use DI\Container;
use Look\Messaging\Message;
use Look\Messaging\MessageBus;
use Look\Messaging\Support\ListOfMessages;
use Tests\Fixtures\TestMessage;
use Tests\Fixtures\TestMessageProvider;

test('can be constructed without container', function () {

    $bus = new MessageBus;

    expect($bus)->not->toBeNull();

});

test('can be constructed with container', function () {

    $bus = new MessageBus(new Container);

    expect($bus)->not->toBeNull();

});

test('container can be set after construction', function () {

    $bus = new MessageBus;

    $bus->setContainer(new Container);

    expect($bus)->not->toBeNull();

});

test('can dispatch array message', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $message = (array) json_decode(json_encode($message), associative: true);
    $handler = $bus->mockHandler($type);

    $bus->dispatch($message);

    expect($handler->messages()->first()->getOriginal())->toEqual($message);
    expect($handler->messages()->count())->toEqual(1);
});

test('can dispatch message provider', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $message = Message::make($type, ['test' => true]);
    $handler = $bus->mockHandler($type);

    $provider = new TestMessageProvider($message);
    $bus->dispatch($provider);

    expect($handler->messages()->first())->toEqual($message);
    expect($handler->messages()->count())->toEqual(1);

});

test('can dispatch type and payload', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $message = Message::make($type, ['test' => true]);
    $handler = $bus->mockHandler($type);

    $bus->dispatch($type, $message->payload());

    $dispatched = $handler->messages()->first();
    expect($dispatched->type())->toEqual($message->type());
    expect($dispatched->payload())->toEqual($message->payload());
    expect($handler->messages()->count())->toEqual(1);
});

test('can receive a list of messages', function () {

    $bus = $this->makeBus();

    $messages = [
        new TestMessage,
        new TestMessage,
    ];

    $bus->mockTransport('mock')
        ->push($messages);

    $received = $bus->receive('mock');

    expect($received)->toBeInstanceOf(ListOfMessages::class);
    expect($received->toArray())->toEqual($messages);

});

test('can receive empty list of messages', function () {

    $bus = $this->makeBus();

    $bus->mockTransport('mock');

    $received = $bus->receive('mock');

    expect($received)->toBeInstanceOf(ListOfMessages::class);
    expect($received->isEmpty())->toBeTrue();

});

test('unknown transport receives empty list of messages', function () {

    $bus = $this->makeBus();

    $received = $bus->receive('unknown');

    expect($received)->toBeInstanceOf(ListOfMessages::class);
    expect($received->isEmpty())->toBeTrue();

});
