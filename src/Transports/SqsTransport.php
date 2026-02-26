<?php

namespace Look\Messaging\Transports;

use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;
use Exception;
use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\Transport;
use Look\Messaging\Support\Env;
use Look\Messaging\Support\Log;

class SqsTransport implements Transport
{
    use Concerns\WithDecorationMethods;
    use Concerns\WithSerializationMethods;

    protected $sqsClient;
    protected array $defaultQueues = [];
    protected bool $withMessageId = true;

    // INSTANTIATION

    public function __construct(array|string $defaultQueues = [], bool $withMessageId = true)
    {
        if (!empty($defaultQueues)) {
            $this->setDefaultQueues($defaultQueues);
        }

        $this->withMessageId($withMessageId);
    }

    public function withMessageId(bool $state): self
    {
        $this->withMessageId = $state;

        return $this;
    }

    public function withoutMessageId(): self
    {
        $this->withMessageId = false;

        return $this;
    }

    // TRANSPORT

    public function send(MessageInterface $message, ?array $args = null): ?bool
    {
        $queues = $args ?? $this->defaultQueues;
        $queues = array_unique(array_filter($queues));

        $relayed = false;
        foreach ($queues as $queue) {
            try {
                $relayed = $relayed || $this->pushToQueue($queue, $message);
            } catch (Exception $e) {
                Log::error($e);
                throw $e;
            }
        }

        return $relayed;
    }

    public function receive(?array $args = null): array
    {
        $queues = $args ?? $this->defaultQueues;
        $queues = array_unique(array_filter($queues));

        $messages = [];
        foreach ($queues as $queue) {
            try {
                $pulled = $this->pullFromQueue($queue);
                $messages = array_merge($messages, $pulled);
            } catch (Exception $e) {
                Log::error($e);
                throw $e;
            }
        }

        return $messages;
    }

    // SQS QUEUE

    public function setDefaultQueues(array|string $queues): self
    {
        $this->defaultQueues = is_string($queues) ? explode(',', $queues) : $queues;

        return $this;
    }

    public function getDefaultQueues(): array
    {
        return $this->defaultQueues;
    }

    protected function pushToQueue(string $queue, MessageInterface $message): bool
    {
        $sqsQueueUrl = Env::get('AWS_SQS_PREFIX').'/'.$queue;

        $sqsClient = $this->getSqsClient();

        $body = $this->decorate(
            $this->serialize($message)
        );

        $envelope = [
            'QueueUrl' => $sqsQueueUrl,
            'MessageBody' => json_encode($body),
        ];

        $isFifoQueue = str_ends_with($queue, '.fifo');

        if ($isFifoQueue) {
            $envelope['MessageGroupId'] = 'default';

            if ($this->withMessageId) {
              $envelope['MessageDeduplicationId'] = (string) $message->id();
            }
        }

        $sqsClient->sendMessage($envelope);

        return true;
    }

    protected function pullFromQueue(string $queue): array
    {
        $sqsQueueUrl = Env::get('AWS_SQS_PREFIX').'/'.$queue;

        $sqsClient = $this->getSqsClient();

        try {
            $response = $sqsClient->receiveMessage([
                'AttributeNames' => ['SentTimestamp'],
                'MaxNumberOfMessages' => 10,
                'MessageAttributeNames' => ['All'],
                'QueueUrl' => $sqsQueueUrl,
            ]);
        } catch (AwsException $e) {
            Log::error($e);
        }

        if (empty($response)) {
            return [];
        }

        $payload = $response->get('Messages');
        if (empty($payload)) {
            return [];
        }

        $messages = [];
        foreach ($payload as $data) {
            if (empty($data['Body'])) {
                continue;
            }

            $message = @json_decode($data['Body']);
            if (!$message) {
                Log::info($data);
                continue;
            }

            try {
                $sqsClient->deleteMessage([
                    'QueueUrl' => $sqsQueueUrl,
                    'ReceiptHandle' => $data['ReceiptHandle'],
                ]);
                $messages[] = $this->unserialize($message);
            } catch (AwsException $e) {
                Log::error($e);
            }
        }

        return $messages;
    }

    public function getSqsClient(): SqsClient
    {
        if (!isset($this->sqsClient)) {
            $config = [
                'version' => 'latest',
            ];

            $region = Env::get('AWS_REGION');
            if ($region) {
                $config['region'] = $region;
            }

            $endpoint = Env::get('AWS_ENDPOINT');
            if ($endpoint) {
                $config['endpoint'] = $endpoint;
            }

            $this->sqsClient = new SqsClient($config);
        }

        return $this->sqsClient;
    }

    public function setSqsClient($client): self
    {
        $this->sqsClient = $client;

        return $this;
    }

    public function dropSqsClient(): self
    {
        unset($this->sqsClient);

        return $this;
    }
}
