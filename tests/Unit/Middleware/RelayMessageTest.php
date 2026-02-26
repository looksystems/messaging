<?php

namespace Tests\Unit\Middleware;

use Look\Messaging\Message;

test('can relay message', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $called = false;
    $bus->relay(
        $type,
        function ($message) use (&$called) {
            $called = true;
        }
    );

    $bus->dispatch($message);

    expect($called)->toBeTrue();
    expect($bus->dispatched()->count())->toEqual(1);
    expect($bus->relayed()->count())->toEqual(1);
    expect($bus->handled()->count())->toEqual(0);

});

test('can relay multiple messages', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $normalised = Message::make($type, $message);

    $relay1 = $bus->mockRelay($type);
    $relay2 = $bus->mockRelay($type);

    $bus->dispatch($normalised);

    expect($bus->handled()->count())->toEqual(0);
    expect($bus->relayed()->count())->toEqual(1);
    expect($relay1->messages()->first())->toEqual($normalised);
    expect($relay1->messages()->count())->toEqual(1);
    expect($relay2->messages()->first())->toEqual($normalised);
    expect($relay2->messages()->count())->toEqual(1);

});

test('fallback relay not called if message already relayed', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $normalised = Message::make($type, $message);

    $relay = $bus->mockRelay($type);
    $fallback = $bus->mockRelay($type, fallback: true);

    $bus->dispatch($normalised);

    expect($bus->handled()->count())->toEqual(0);
    expect($bus->relayed()->count())->toEqual(1);
    expect($relay->messages()->first())->toEqual($normalised);
    expect($relay->messages()->count())->toEqual(1);
    expect($fallback->messages()->count())->toEqual(0);

});

test('fallback relay not called if message already handled', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $normalised = Message::make($type, $message);

    $handler = $bus->mockHandler($type);
    $relay = $bus->mockRelay($type.':fallback');

    $bus->dispatch($normalised);

    expect($bus->handled()->count())->toEqual(1);
    expect($bus->relayed()->count())->toEqual(0);
    expect($handler->messages()->first())->toEqual($normalised);
    expect($handler->messages()->count())->toEqual(1);
    expect($relay->messages()->count())->toEqual(0);

});
