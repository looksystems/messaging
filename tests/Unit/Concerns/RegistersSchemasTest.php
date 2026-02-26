<?php

namespace Tests\Concerns;

use Look\Messaging\Exceptions\NoSchemaException;
use Look\Messaging\Validators\JsonSchema;

test('can register schema definition for message type', function () {

    $bus = $this->makeBus();
    $bus->schema(
        'message.type',
        [
            'id' => 'https://example.com/shop/events/order-completed.json',
            'path' => $this->resources('schemas/shop/events/order-completed.json'),
        ]
    );

    $schemas = $bus->listOfValidators();

    expect(count($schemas))->toEqual(1);
    expect($schemas[0])->toBeInstanceOf(JsonSchema::class);

});

test('can register schema file for message type', function () {

    $bus = $this->makeBus();
    $bus->schema(
        'message.type',
        'https://example.com/shop/events/order-completed.json',
        'file:'.$this->resources('schemas/shop/events/order-completed.json')
    );

    $schemas = $bus->listOfValidators();

    expect(count($schemas))->toEqual(1);
    expect($schemas[0])->toBeInstanceOf(JsonSchema::class);

});

test('can register schema directory for message type', function () {

    $bus = $this->makeBus();
    $bus->schema(
        'message.*',
        'https://example.com',
        'dir:'.$this->resources('schemas/')
    );

    $schemas = $bus->listOfValidators();

    expect(count($schemas))->toEqual(1);
    expect($schemas[0])->toBeInstanceOf(JsonSchema::class);

});

test('can register raw json schema for message type', function () {

    $bus = $this->makeBus();
    $bus->schema(
        'message.*',
        'https://example.com',
        file_get_contents($this->resources('schemas/shop/events/order-completed.json'))
    );

    $schemas = $bus->listOfValidators();

    expect(count($schemas))->toEqual(1);
    expect($schemas[0])->toBeInstanceOf(JsonSchema::class);

});

test('throws exception when no schema provided', function () {

    $bus = $this->makeBus();
    $bus->schema(
        'message.*',
        'https://example.com',
        ''
    );

})
    ->throws(NoSchemaException::class);

test('can register list of schemas', function () {

    $bus = $this->makeBus();
    $bus->schemas([
        'message.*' => [
            'id' => 'https://example.com/shop/events/order-completed.json',
            'path' => $this->resources('schemas/shop/events/order-completed.json'),
        ],
    ]);

    $schemas = $bus->listOfValidators();

    expect(count($schemas))->toEqual(1);
    expect($schemas[0])->toBeInstanceOf(JsonSchema::class);

});

test('registerSchemas is alias for schemas method', function () {

    $bus = $this->makeBus();
    $bus->registerSchemas([
        'message.*' => [
            'id' => 'https://example.com/shop/events/order-completed.json',
            'path' => $this->resources('schemas/shop/events/order-completed.json'),
        ],
    ]);

    $schemas = $bus->listOfValidators();

    expect(count($schemas))->toEqual(1);
    expect($schemas[0])->toBeInstanceOf(JsonSchema::class);

});

test('can register schemas for list of types', function () {

    $bus = $this->makeBus();
    $bus->schemas(
        [
            'message.type-1',
            'message.type-2',
        ],
        [
            'id' => 'https://example.com/shop/events/order-completed.json',
            'path' => $this->resources('schemas/shop/events/order-completed.json'),
        ]
    );

    $schemas = $bus->listOfValidators();

    expect(count($schemas))->toEqual(2);
    expect($schemas[0])->toBeInstanceOf(JsonSchema::class);
    expect($schemas[0])->toEqual($schemas[1]);

});

test('can register single schema', function () {

    $bus = $this->makeBus();
    $bus->schemas(
        'message.*',
        [
            'id' => 'https://example.com/shop/events/order-completed.json',
            'path' => $this->resources('schemas/shop/events/order-completed.json'),
        ]
    );

    $schemas = $bus->listOfValidators();

    expect(count($schemas))->toEqual(1);
    expect($schemas[0])->toBeInstanceOf(JsonSchema::class);

});
