<?php

namespace Tests\Concerns;

test('supports deferred handler registation', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $called = false;
    $bus->booting(function ($bus) use ($type, &$called) {
        $bus->handle(
            $type,
            function ($message) use (&$called) {
                $called = true;
            }
        );
    });

    $bus->dispatch($message);

    expect($called)->toBeTrue();

});
