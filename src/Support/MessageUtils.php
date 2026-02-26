<?php

namespace Look\Messaging\Support;

use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\ProvidesMessage;
use Look\Messaging\Message;

class MessageUtils
{
    public static function cast(object|array|string $message): MessageInterface
    {
        if (is_array($message)) {
            return self::fromArray($message);
        }

        if ($message instanceof MessageInterface) {
            return $message;
        }

        if ($message instanceof ProvidesMessage) {
            return $message->toMessage();
        }

        return self::fromJson($message);
    }

    public static function fromArray(array $array, bool $keepOriginal = false): Message
    {
        $id = $array['id'] ?? null;
        $type = $array['type'] ?? null;
        $version = $array['version'] ?? null;
        $payload = $array['payload'] ?? [];
        $timestamp = $array['timestamp'] ?? null;

        $message = new Message($type, $payload, $id, $version, $timestamp);

        $test = $array['test'] ?? null;
        if ($test) {
            $message->markAsTest();
        }

        $message->setSystem($array['system'] ?? null);

        if ($keepOriginal) {
            $message->setOriginal($array);
        }

        return $message;
    }

    public static function fromJson(object|string $json, bool $keepOriginal = false): Message
    {
        $original = is_string($json) ? json_decode($json) : $json;

        $id = $original?->id;
        $type = $original?->type;
        $version = $original?->version;
        $payload = $original?->payload ?? [];
        $timestamp = $original->timestamp ?? null;

        $message = new Message($type, $payload, $id, $version, $timestamp);

        $test = $original->test ?? null;
        if ($test) {
            $message->markAsTest();
        }

        $message->setSystem($original?->system ?? null);

        if ($keepOriginal) {
            $message->setOriginal($original);
        }

        return $message;
    }

    public static function toJson(MessageInterface $message, bool $encode = false): object|string
    {
        $data = (object) [
            'id' => $message->id(),
            'type' => $message->type(),
            'version' => $message->version(),
            'timestamp' => $message->timestamp()?->format('Y-m-d\\TH:i:s.uP'),
            'payload' => $message->payload(),
        ];

        if ($message->envelope()->hasStamps()) {
            $data->envelope = $message->envelope()->listOfStamps();
        }

        if ($message->isTest()) {
            $data->test = $message->isTest();
        }

        if ($message->system()) {
            $data->system = $message->system();
        }

        return $encode ? json_encode($data) : $data;
    }
}
