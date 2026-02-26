<?php

namespace Tests\Unit\Handlers;

use Look\Messaging\Handlers\Dedupe;

test('it will only relay first copy of a message', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $bus->handle('*', Dedupe::usingFirst());
    $relay = $bus->mockRelay('*');

    $bus->dispatch($message);
    $bus->dispatch($message);

    expect($bus->dispatched()->count())->toEqual(1);
    expect($bus->relayed()->count())->toEqual(1);
    expect($bus->handled()->count())->toEqual(0);

});

test('it will only relay last copy of a message', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $dedupe = Dedupe::usingLast();

    $bus->handle('*', $dedupe);
    $relay = $bus->mockRelay('*');

    $bus->dispatch($message);
    expect($bus->dispatched()->count())->toEqual(0);
    expect($bus->relayed()->count())->toEqual(0);
    expect($bus->handled()->count())->toEqual(0);
    expect($relay->called())->toEqual(0);

    $bus->dispatch($message);
    expect($bus->dispatched()->count())->toEqual(0);
    expect($bus->relayed()->count())->toEqual(0);
    expect($bus->handled()->count())->toEqual(0);
    expect($relay->called())->toEqual(0);

    // emulate shutdown callback
    Dedupe::flush();
    expect($bus->dispatched()->count())->toEqual(1);
    expect($bus->relayed()->count())->toEqual(1);
    expect($bus->handled()->count())->toEqual(0);
    expect($relay->called())->toEqual(1);
});

test('it can handle dispatchFirst duplicates', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $duplicates = 0;
    $dedupe = Dedupe::usingFirst()
        ->handleDuplicatesUsing(function ($message) use (&$duplicates) {
            $duplicates++;
        });

    $bus->handle('*', $dedupe);
    $bus->mockRelay('*');

    $bus->dispatch($message);
    $bus->dispatch($message);
    $bus->dispatch($message);

    expect($duplicates)->toEqual(2);

});

test('it can handle dispatchLast duplicates', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $duplicates = 0;
    $dedupe = Dedupe::usingLast()
        ->handleDuplicatesUsing(function ($message) use (&$duplicates) {
            $duplicates++;
        });

    $bus->handle('*', $dedupe);
    $bus->mockRelay('*');

    $bus->dispatch($message);
    $bus->dispatch($message);
    $bus->dispatch($message);

    expect($duplicates)->toEqual(2);

});
