<?php

use Look\Messaging\Support\Env;
use Look\Messaging\Transports\SnsTransport;
use Tests\Fixtures\TestMessage;

test('it can send a message', function () {

    $message = new TestMessage;
    $transport = new SnsTransport;
    $topic = Env::get('TEST_SNS_TOPIC');

    $relayed = $transport->send($message, [$topic]);

    expect($relayed)->toBeTrue();

})->skip();
