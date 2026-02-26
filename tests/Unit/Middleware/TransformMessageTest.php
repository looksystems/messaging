<?php

namespace Tests\Unit\Middleware;

test('can transform message', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();

    $handler = $bus->mockHandler($type);
    $bus->transform('*', function ($message) {
        $message->transformed = true;
        return $message;
    });

    $bus->dispatch($message);

    expect($bus->dispatched()->first()->transformed)->toBeTrue();

});
