<?php

namespace Tests\Unit\Decorators;

use Look\Messaging\Decorators\StandardDecorator;
use Look\Messaging\Message;

test('it can decorate message data', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $data = $message->jsonSerialize();

    $decorator = new StandardDecorator;
    $decorator->add(['system' => 'dev']);
    $decorated = $decorator->decorate($data);

    $data->system = 'dev';

    expect($decorated)->toEqual($data);

});

test('it can promote envelope stamps', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $message->envelope()->applyStamp('system', 'dev');
    $data = $message->jsonSerialize();

    $decorator = new StandardDecorator;
    $decorator->add('envelope:system');
    $decorated = $decorator->decorate($data);

    $data->system = 'dev';

    expect($decorated)->toEqual($data);

});
