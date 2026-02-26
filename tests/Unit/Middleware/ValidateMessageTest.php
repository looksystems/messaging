<?php

namespace Tests\Unit\Middleware;

use Look\Messaging\Exceptions\InvalidMessageException;
use Look\Messaging\Exceptions\InvalidSchemaException;
use Look\Messaging\Exceptions\NoSchemaException;
use Look\Messaging\MessageBus;
use Look\Messaging\Validators\JsonSchema;
use Look\Messaging\Validators\ValidationActions;
use Tests\Fixtures\EventResolver;

test('it can validate messages', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $bus->setTypeResolver(new EventResolver);
    $schema = JsonSchema::prefix(
        'https://example.com/',
        $this->resources('schemas')
    );
    $bus->validate('*', $schema);

    $bus = new MessageBus(resolver: new EventResolver);

    foreach (glob($this->resources('/events/*')) as $filepath) {
        $message = json_decode(file_get_contents($filepath), false);
        $bus->dispatch($message);

        expect(true)->toBeTrue();
    }

});

test('it will continue if schema message has no schema id', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $schema = JsonSchema::prefix(
        'https://example.com/',
        $this->resources('schemas')
    );
    $bus->validate('*', $schema);

    $bus->dispatch(['_type' => 'unknown', 'data' => 'no type']);

    expect($bus->dispatched()->count())->toEqual(1);

});

test('it will continue if schema not found', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $schema = JsonSchema::prefix(
        'https://example.com/',
        $this->resources('schemas')
    );
    $bus->validate('*', $schema);

    $bus->dispatch(['_type' => 'unknown']);

    expect($bus->dispatched()->count())->toEqual(1);

});

test('it can drop message if schema message has no schema id', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $schema = JsonSchema::prefix(
        'https://example.com/',
        $this->resources('schemas')
    );

    $bus->actions(ValidationActions::drop());
    $bus->dispatch(['_type' => 'unknown', 'data' => 'no type']);

    expect($bus->dispatched()->count())->toEqual(0);

});

test('it can drop messages if schema not registered', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $schema = JsonSchema::prefix(
        'https://example.com/',
        $this->resources('schemas')
    );

    $bus->actions(ValidationActions::drop());
    $bus->dispatch(['_type' => 'unknown']);

    expect($bus->dispatched()->count())->toEqual(0);
    expect($bus->handled()->count())->toEqual(0);
    expect($bus->relayed()->count())->toEqual(0);

});

test('it can drop message if schema not found', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $schema = JsonSchema::prefix(
        'https://example.com/',
        $this->resources('schemas')
    );

    $bus->actions(ValidationActions::drop());
    $bus->dispatch(['_type' => 'unknown']);

    expect($bus->dispatched()->count())->toEqual(0);

});

test('it will throw exception if message fails schema validation', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $bus->setTypeResolver(new EventResolver);
    $schema = JsonSchema::prefix(
        'https://example.com/',
        $this->resources('schemas')
    );
    $bus->validate('*', $schema);

    $bus->dispatch([
        'entity_type' => 'order',
        'event_type' => 'completed',
    ]);

})
    ->throws(InvalidMessageException::class);

test('it can throw if schema message has no schema id', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $bus->actions(ValidationActions::throw());
    $schema = JsonSchema::prefix(
        'https://example.com/',
        $this->resources('schemas')
    );

    $bus->validate('*', $schema);
    $bus->dispatch(['_type' => 'unknown', 'data' => 'no type']);
})
    ->throws(NoSchemaException::class);

test('it can throw exception if schema not found', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $bus->setTypeResolver(new EventResolver);
    $schema = JsonSchema::prefix(
        'https://example.com/',
        $this->resources('schemas')
    );

    $bus->validate('*', $schema, ValidationActions::throw());
    $bus->dispatch([
        'entity_type' => 'unknown',
        'event_type' => 'unknown',
    ]);
})
    ->throws(NoSchemaException::class);

test('it can throw exception if schema is invalid', function () {

    [$bus, $message, $type] = $this->makeBusAndMessage();
    $bus->setTypeResolver(new EventResolver);

    $schema = JsonSchema::file(
        'https://example.com/shop/events/order-completed.json',
        $this->resources('/schemas/invalid.json')
    );
    $schema->setSchemaPrefix('https://example.com/');

    $bus->validate('*', $schema);

    $filepath = $this->resources('/events/1001.json');
    $message = json_decode(file_get_contents($filepath), false);

    $bus->dispatch($message);
})
    ->throws(InvalidSchemaException::class);
