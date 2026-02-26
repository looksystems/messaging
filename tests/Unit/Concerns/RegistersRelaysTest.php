<?php

namespace Tests\Concerns;

use Closure;
use Look\Messaging\Mock\MockRelay;
use Tests\Fixtures\TestRelay;

test('can register relay for message type', function () {

    $bus = $this->makeBus();
    $bus->relay('message.type', function ($message) {
        return $message;
    });

    $relays = $bus->listOfRelays();

    expect(count($relays))->toEqual(1);
    expect($relays[0])->toBeInstanceOf(Closure::class);

});

test('can register relay for multiple message types', function () {

    $bus = $this->makeBus();
    $bus->relay(
        [
            'message.type-1',
            'message.type-2',
        ],
        function ($message) {
            return $message;
        }
    );

    $relays = $bus->listOfRelays();

    expect(count($relays))->toEqual(1);
    expect($relays[0])->toBeInstanceOf(Closure::class);

});

test('can register list of relays', function () {

    $bus = $this->makeBus();
    $bus->relay([
        'message.type-1' => MockRelay::class,
        'message.type-2' => TestRelay::class,
    ]);

    $relays = $bus->listOfRelays();

    expect(count($relays))->toEqual(2);
    expect($relays[0])->toEqual(MockRelay::class);
    expect($relays[1])->toEqual(TestRelay::class);

});

test('can register mock relay', function () {

    $bus = $this->makeBus();
    $bus->mockRelay('message.type');

    $relays = $bus->listOfRelays();

    expect(count($relays))->toEqual(1);
    expect($relays[0])->toBeInstanceOf(MockRelay::class);

});

test('can list relays', function () {

    $bus = $this->makeBus();

    $bus->mockRelay('message.type');
    $bus->relay('message.type', TestRelay::class);
    $bus->relay('message.type', function ($message) {
        return $message;
    });
    $bus->registerRelays([
        'message.custom-1' => new TestRelay,
    ]);

    $relays = $bus->listOfRelays();

    expect(count($relays))->toEqual(4);
    expect($relays[0])->toBeInstanceOf(MockRelay::class);
    expect($relays[1])->toEqual(TestRelay::class);
    expect($relays[2])->toBeInstanceOf(Closure::class);
    expect($relays[3])->toBeInstanceOf(TestRelay::class);

});
