<?php

namespace Look\Messaging;

use ArrayAccess;
use ArrayObject;
use DateTime;
use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Support\MessageUtils;
use Iterator;
use IteratorAggregate;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

class Message implements ArrayAccess, IteratorAggregate, JsonSerializable, MessageInterface
{
    protected string|int $id;
    protected string $type;
    protected string $version;
    protected DateTime $timestamp;
    protected ArrayObject $payload;
    protected Envelope $envelope;
    protected object|array|null $original = null;
    protected bool $test = false;
    protected ?string $system = null;

    // INSTANTIATION

    public static function make(string $type, array|object $payload = [], string|int|null $id = null, string $version = '1', DateTime|string|null $timestamp = null): Message
    {
        return new self($type, $payload, $id, $version, $timestamp);
    }

    public function __construct(string $type, array|object $payload = [], string|int|null $id = null, string $version = '1', DateTime|string|null $timestamp = null)
    {
        $this->type = $type;
        $this->version = $version;
        $this->id = $id ?? (string) Uuid::uuid4();
        $this->payload = new ArrayObject($payload);
        $this->envelope = new Envelope;

        if (is_string($timestamp)) {
            $timestamp = $timestamp ? new DateTime($timestamp) : null;
        }
        $this->timestamp = $timestamp ?? new DateTime;
    }

    // ENVELOPE

    public function envelope(): Envelope
    {
        return $this->envelope;
    }

    public function applyStamp(string $name, mixed $value): self
    {
        $this->envelope->applyStamp($name, $value);

        return $this;
    }

    public function applyStamps(Envelope|array $listOfStamps): self
    {
        $this->envelope->applyStamps($listOfStamps);

        return $this;
    }

    public function removeStamps(...$listOfNames): self
    {
        $this->envelope->removeStamps($listOfNames);

        return $this;
    }

    // MESSAGE

    public function id(): string|int
    {
        return $this->id;
    }

    public function setId(string|int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function timestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTime|string $timestamp): self
    {
        if (is_string($timestamp)) {
            $timestamp = $timestamp ? new DateTime($timestamp) : null;
        }
        $this->timestamp = $timestamp;

        return $this;
    }

    public function system(): ?string
    {
        return $this->system;
    }

    public function setSystem(?string $system): self
    {
        $this->system = $system;

        return $this;
    }

    public function isTest(): bool
    {
        return $this->test;
    }

    public function markAsTest(bool $state = true): self
    {
        $this->test = $state;

        return $this;
    }

    // PAYLOAD

    public function merge(object|array $payload): self
    {
        foreach ($payload as $key => $value) {
            $this->payload[$key] = $value;
        }

        return $this;
    }

    public function setPayload(object|array $payload): self
    {
        $this->payload->exchangeArray($payload);

        return $this;
    }

    public function payload(): object
    {
        return (object) $this->payload->getArrayCopy();
    }

    // ARRAY ACCESS

    public function offsetGet(mixed $offset): mixed
    {
        return $this->payload[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->payload[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->payload[$offset]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->payload[$offset]);
    }

    // PROPERTY ACCESS

    public function __get(string $name): mixed
    {
        return $this->payload[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->payload[$name] = $value;
    }

    public function __unset(string $name): void
    {
        unset($this->payload[$name]);
    }

    public function __isset(string $name): bool
    {
        return isset($this->payload[$name]);
    }

    // ITERATOR

    public function getIterator(): Iterator
    {
        return $this->payload->getIterator();
    }

    // JSON

    public function toJson(): string
    {
        return json_encode($this->jsonSerialize());
    }

    public function jsonSerialize(): mixed
    {
        return MessageUtils::toJson($this);
    }

    // ORIGINALS

    public function getOriginal(): mixed
    {
        return $this->original;
    }

    public function setOriginal(object|array $message): self
    {
        $this->original = $message;

        return $this;
    }
}
