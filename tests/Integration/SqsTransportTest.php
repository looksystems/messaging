<?php

use Look\Messaging\Support\Env;
use Look\Messaging\Transports\SqsTransport;
use Tests\Fixtures\TestMessage;

test('it can send a message', function () {

    $message = new TestMessage;
    $transport = new SqsTransport;
    $queue = Env::get('TEST_SQS_QUEUE');

    $relayed = $transport->send($message, [$queue]);

    expect($relayed)->toBeTrue();

})->skip();
