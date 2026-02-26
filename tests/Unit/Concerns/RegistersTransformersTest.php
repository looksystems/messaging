<?php

namespace Tests\Concerns;

use Closure;
use Look\Messaging\Mock\MockTransformer;
use Tests\Fixtures\TestTransformer;

test('can register transformer for message type', function () {

    $bus = $this->makeBus();
    $bus->transform('message.type', function ($message) {
        return $message;
    });

    $transformers = $bus->listOfTransformers();

    expect(count($transformers))->toEqual(1);
    expect($transformers[0])->toBeInstanceOf(Closure::class);

});

test('can register transformer for multiple message types', function () {

    $bus = $this->makeBus();
    $bus->transform(
        [
            'message.type-1',
            'message.type-2',
        ],
        function ($message) {
            return $message;
        }
    );

    $transformers = $bus->listOfTransformers();

    expect(count($transformers))->toEqual(1);
    expect($transformers[0])->toBeInstanceOf(Closure::class);

});

test('can register list of transformers', function () {

    $bus = $this->makeBus();
    $bus->transform([
        'message.type-1' => MockTransformer::class,
        'message.type-2' => TestTransformer::class,
    ]);

    $transformers = $bus->listOfTransformers();

    expect(count($transformers))->toEqual(2);
    expect($transformers[0])->toEqual(MockTransformer::class);
    expect($transformers[1])->toEqual(TestTransformer::class);

});

test('can register mock transformer', function () {

    $bus = $this->makeBus();
    $bus->mockTransformer('message.type');

    $transformers = $bus->listOfTransformers();

    expect(count($transformers))->toEqual(1);
    expect($transformers[0])->toBeInstanceOf(MockTransformer::class);

});

test('can list transformers', function () {

    $bus = $this->makeBus();

    $bus->mockTransformer('message.type');
    $bus->transform('message.type', TestTransformer::class);
    $bus->transform('message.type', function ($message) {
        return $message;
    });
    $bus->registerTransformers([
        'message.custom-1' => new TestTransformer,
    ]);

    $transformers = $bus->listOfTransformers();

    expect(count($transformers))->toEqual(4);
    expect($transformers[0])->toBeInstanceOf(MockTransformer::class);
    expect($transformers[1])->toEqual(TestTransformer::class);
    expect($transformers[2])->toBeInstanceOf(Closure::class);
    expect($transformers[3])->toBeInstanceOf(TestTransformer::class);

});
