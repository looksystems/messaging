<?php

namespace Tests\Unit\Resolvers;

use Look\Messaging\Resolvers\MessageProperty;
use Tests\Fixtures\TestMessage;

test('resolves message type from property', function () {

    $resolver = new MessageProperty;
    $message = new TestMessage;

    $type = $resolver->type($message);

    expect($type)->toBeString()->toEqual($message->_type);

});

test('exact matches message type from list', function () {

    $resolver = new MessageProperty;
    $list = [
        'test' => 1,
        'test.testing' => 2,
        'testing.test' => 3,
    ];

    $found = $resolver->match('testing.test', $list);

    expect($found)->toBeArray()->toEqual([3]);

});

test('wildcard matches message types from simple list', function () {

    $resolver = new MessageProperty;
    $list = [
        '*' => 1,
        'testing.*' => 2,
        'testing.test.*' => 3,
    ];

    $found = $resolver->match('testing.test.subtest', $list);

    expect($found)->toBeArray()->toEqual([3, 2, 1]);

});

test('wildcard matches message types from list of arrays', function () {

    $resolver = new MessageProperty;
    $list = [
        '*' => [1, 2],
        'testing.*' => [3],
        'testing.test.*' => [4, 5],
    ];

    $found = $resolver->match('testing.test.subtest', $list);

    expect($found)->toBeArray()->toEqual([4, 5, 3, 1, 2]);

});
