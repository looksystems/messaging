<?php

namespace Look\Messaging\Transports;

use Aws\Sns\SnsClient;
use Exception;
use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\Transport;
use Look\Messaging\Support\Env;
use Look\Messaging\Support\Log;

class SnsTransport implements Transport
{
    use Concerns\WithDecorationMethods;
    use Concerns\WithSerializationMethods;

    protected $snsClient;
    protected array $defaultTopics = [];
    protected $withMessageId = true;

    // INSTANTIATION

    public function __construct(array|string $defaultTopics = [], bool $withMessageId = true)
    {
        if (!empty($defaultTopics)) {
            $this->setDefaultTopics($defaultTopics);
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
        $topics = $args ?? $this->defaultTopics;
        $topics = array_unique(array_filter($topics));

        $relayed = false;
        foreach ($topics as $topic) {
            try {
                $this->pushToTopic($topic, $message);
                $relayed = true;
            } catch (Exception $e) {
                Log::error($e);
                throw $e;
            }
        }

        return $relayed;
    }

    public function receive(?array $args = null): array
    {
        throw new Exception('Not implemented');
    }

    // SNS TOPICS

    public function setDefaultTopics(array|string $topics): self
    {
        $this->defaultTopics = is_string($topics) ? explode(',', $topics) : $topics;

        return $this;
    }

    public function getDefaultTopics(): array
    {
        return $this->defaultTopics;
    }

    protected function pushToTopic(string $topic, MessageInterface $message): void
    {
        $snsTopicArn = Env::get('AWS_SNS_PREFIX').':'.$topic;

        $snsClient = $this->getSnsClient();

        $body = $this->decorate(
            $this->serialize($message)
        );

        $envelope = [
            'TopicArn' => $snsTopicArn,
            'Message' => json_encode($body),
        ];

        $isFifoTopic = str_ends_with($topic, '.fifo');

        if ($isFifoTopic) {
            $envelope['MessageGroupId'] = 'default';
            
            if ($this->withMessageId) {
              $envelope['MessageDeduplicationId'] = (string) $message->id();
            }
        }

        $snsClient->publish($envelope);
    }

    public function getSnsClient(): SnsClient
    {
        if (!isset($this->snsClient)) {
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

            $this->snsClient = new SnsClient($config);
        }

        return $this->snsClient;
    }

    public function setSnsClient($client): self
    {
        $this->snsClient = $client;

        return $this;
    }

    public function dropSnsClient(): self
    {
        unset($this->snsClient);

        return $this;
    }
}
