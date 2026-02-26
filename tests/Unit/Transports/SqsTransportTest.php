<?php

namespace Tests\Unit\Transports;

use Aws\Result;
use Aws\Sqs\SqsClient;
use Look\Messaging\Decorators\StandardDecorator;
use Look\Messaging\Message;
use Look\Messaging\Support\Env;
use Look\Messaging\Support\MessageUtils;
use Look\Messaging\Transports\SqsTransport;
use Mockery;

function mockSqsTransport(?string $defaultQueue = null): SqsTransport
{
    if (!$defaultQueue) {
        $defaultQueue = Env::get('TEST_SQS_QUEUE');
    }

    $sqsClient = Mockery::mock(SqsClient::class);

    return (new SqsTransport($defaultQueue))
        ->setSqsClient($sqsClient)
        ->withoutMessageId();
}

function mockSqsSend(?SqsTransport $transport = null, ?Message $message = null, ?string $queue = null, array $decorations = []): SqsTransport
{
    if (!$queue) {
        $queue = Env::get('TEST_SQS_QUEUE');
    }

    if (!$transport) {
        $transport = mockSqsTransport();
    }

    $sqsClient = $transport->getSqsClient();
    $sendMessage = $sqsClient->shouldReceive('sendMessage');

    if ($message) {
        $message = MessageUtils::cast($message);

        $data = MessageUtils::toJson($message);
        if ($decorations) {
            $data = StandardDecorator::apply($data, $decorations);
        }

        $sendMessage->with([
            'MessageBody' => json_encode($data),
            'QueueUrl' => Env::get('AWS_SQS_PREFIX').'/'.$queue,
        ]);
    }

    return $transport;
}

function mockSqsReceive($transport = null, array $messages = [], ?string $queue = null): SqsTransport
{
    if (!$queue) {
        $queue = Env::get('TEST_SQS_QUEUE');
    }

    if (!$transport) {
        $transport = mockSqsTransport();
    }

    $payload = [];
    foreach ($messages as $message) {
        $message = MessageUtils::cast($message);
        $payload[] = [
            'Body' => json_encode(MessageUtils::toJson($message)),
            'ReceiptHandle' => uniqid(),
        ];
    }

    $sqsClient = $transport->getSqsClient();

    $sqsClient
        ->shouldReceive('receiveMessage')
        ->with([
            'AttributeNames' => ['SentTimestamp'],
            'MaxNumberOfMessages' => 10,
            'MessageAttributeNames' => ['All'],
            'QueueUrl' => Env::get('AWS_SQS_PREFIX').'/'.$queue,
        ])
        ->andReturn(new Result(['Messages' => $payload]));

    $sqsClient->shouldReceive('deleteMessage');

    return $transport;
}

test('it can be constructed with a default queue', function () {

    $queue = Env::get('TEST_SQS_QUEUE');
    $transport = new SqsTransport($queue);

    expect($transport)->not->toBeNull();
    expect($transport->getDefaultQueues())->toEqual([$queue]);

});

test('it can send a message', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $transport = mockSqsSend(message: $message);

    $relayed = $transport->send($message);

    expect($relayed)->toBeTrue();

});

test('it will can send to alternative queues', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $transport = mockSqsSend(message: $message, queue: 'alternative');

    $relayed = $transport->send($message, ['alternative']);

    expect($relayed)->toBeTrue();

});

test('it will not send if no queue is provided', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $transport = new SqsTransport;

    $relayed = $transport->send($message);

    expect($relayed)->toBeFalse();

});

test('it will can send to using decorator', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $transport = mockSqsSend(message: $message, decorations: ['environment' => Env::get('APP_ENV')]);
    $transport->decorateUsing(['environment' => Env::get('APP_ENV')]);

    $relayed = $transport->send($message);

    expect($relayed)->toBeTrue();

});

test('it can receive messages', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $transport = mockSqsReceive(messages: [$message]);

    $received = $transport->receive();

    expect($received)->toEqual([$message]);

});

test('it can receive no messages', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $transport = mockSqsReceive(messages: []);

    $received = $transport->receive();

    expect($received)->toEqual([]);

});

test('it can receive messages via message bus', function () {

    $message = Message::make('testing.test', ['test' => true]);
    $transport = mockSqsReceive(messages: [$message]);

    $bus = $this->makeBus();
    $bus->registerTransport('sqs', $transport);

    $received = $bus->receive('sqs');

    expect($received->toArray())->toEqual([$message]);

});
