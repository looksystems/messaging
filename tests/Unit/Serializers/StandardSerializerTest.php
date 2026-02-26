<?php

namespace Tests\Unit\Serializers;

use Look\Messaging\Message;
use Look\Messaging\Serializers\StandardSerializer;

test('it serializes a message to data', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $data = $message->jsonSerialize();

    $serializer = new StandardSerializer;
    $serialized = $serializer->serialize($message);

    expect($serialized)->toEqual($data);

});

test('it unserializes data to message', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $data = $message->jsonSerialize();

    $serializer = new StandardSerializer;
    $unserialized = $serializer->unserialize($data);

    expect($unserialized)->toEqual($message);

});
