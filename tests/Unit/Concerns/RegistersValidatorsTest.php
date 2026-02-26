<?php

namespace Tests\Concerns;

use Closure;
use Look\Messaging\Mock\MockValidator;
use Tests\Fixtures\TestValidator;

test('can register validator for message type', function () {

    $bus = $this->makeBus();
    $bus->validate('message.type', function ($message) {
        return $message;
    });

    $validators = $bus->listOfValidators();

    expect(count($validators))->toEqual(1);
    expect($validators[0])->toBeInstanceOf(Closure::class);

});

test('can register fallback validator for message type', function () {

    $bus = $this->makeBus();
    $bus->validate('message.type:fallback', function ($message) {
        return $message;
    });

    $validators = $bus->listOfValidators(fallback: true);
    expect(count($validators))->toEqual(1);
    expect($validators[0])->toBeInstanceOf(Closure::class);

    $validators = $bus->listOfValidators(fallback: false);
    expect(count($validators))->toEqual(0);

});

test('can register validator for multiple message types', function () {

    $bus = $this->makeBus();
    $bus->validate(
        [
            'message.type-1',
            'message.type-2',
        ],
        function ($message) {
            return $message;
        }
    );

    $validators = $bus->listOfValidators();

    expect(count($validators))->toEqual(1);
    expect($validators[0])->toBeInstanceOf(Closure::class);

});

test('can register list of validators', function () {

    $bus = $this->makeBus();
    $bus->validate([
        'message.type-1' => MockValidator::class,
        'message.type-2' => TestValidator::class,
    ]);

    $validators = $bus->listOfValidators();

    expect(count($validators))->toEqual(2);
    expect($validators[0])->toEqual(MockValidator::class);
    expect($validators[1])->toEqual(TestValidator::class);

});

test('can register mock validator', function () {

    $bus = $this->makeBus();
    $bus->mockValidator('message.type');

    $validators = $bus->listOfValidators();

    expect(count($validators))->toEqual(1);
    expect($validators[0])->toBeInstanceOf(MockValidator::class);

});

test('can list validators', function () {

    $bus = $this->makeBus();

    $bus->mockValidator('message.type');
    $bus->validate('message.type', TestValidator::class);
    $bus->validate('message.type', function ($message) {
        return $message;
    });
    $bus->registerValidators([
        'message.custom-1' => new TestValidator,
    ]);

    $validators = $bus->listOfValidators();

    expect(count($validators))->toEqual(4);
    expect($validators[0])->toBeInstanceOf(MockValidator::class);
    expect($validators[1])->toEqual(TestValidator::class);
    expect($validators[2])->toBeInstanceOf(Closure::class);
    expect($validators[3])->toBeInstanceOf(TestValidator::class);

});
