<?php

namespace Tests\Unit\Transports;

use Aws\Sns\SnsClient;
use Look\Messaging\Message;
use Look\Messaging\Support\Env;
use Look\Messaging\Transports\SnsTransport;
use Mockery;

function mockSnsTransport(
    ?string $defaultTopic = null
): SnsTransport {
    if (!$defaultTopic) {
        $defaultTopic = Env::get('TEST_SNS_TOPIC');
    }

    $snsClient = Mockery::mock(SnsClient::class);

    return (new SnsTransport($defaultTopic))
        ->setSnsClient($snsClient)
        ->withoutMessageId();
}

function mockSnsPublish(
    ?SnsTransport $transport = null,
    ?object $message = null,
    ?string $topic = null
): SnsTransport {
    if (!$topic) {
        $topic = Env::get('TEST_SNS_TOPIC');
    }

    if (!$transport) {
        $transport = mockSnsTransport();
    }

    $snsClient = $transport->getSnsClient();
    $receive = $snsClient->shouldReceive('publish');

    if ($message) {
        $receive->with([
            'TopicArn' => Env::get('AWS_SNS_PREFIX').':'.$topic,
            'Message' => json_encode($message),
        ]);
    }

    return $transport;
}

test('it can be constructed with a default topic', function () {

    $topic = Env::get('TEST_SNS_TOPIC');
    $transport = new SnsTransport($topic);

    expect($transport)->not->toBeNull()
        ->and($transport->getDefaultTopics())->toEqual([$topic]);

});

test('it can send a message', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $transport = mockSnsPublish(message: $message);

    $relayed = $transport->send($message);

    expect($relayed)->toBeTrue();

});

test('it will not send if no topic is provided', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $transport = new SnsTransport;

    $relayed = $transport->send($message);

    expect($relayed)->toBeFalse();

});

test('it can send to alternative topics', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $transport = mockSnsPublish(message: $message, topic: 'alternative');

    $relayed = $transport->send($message, ['alternative']);

    expect($relayed)->toBeTrue();

});
