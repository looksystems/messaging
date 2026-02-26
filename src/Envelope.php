<?php

namespace Look\Messaging;

use ArrayAccess;
use ArrayIterator;
use DateTime;
use Iterator;
use IteratorAggregate;

/*
 * Work-in-progress: may be subject to change
 */
class Envelope implements ArrayAccess, IteratorAggregate
{
    protected array $stamps = [];

    // INSTANTIATION

    public static function make(?array $stamps = []): self
    {
        $envelope = new self;
        if ($stamps) {
            $envelope->applyStamps($stamps);
        }

        return $envelope;
    }

    // STAMPS

    public function stamp(string $name, $default = null): mixed
    {
        return $this->stamps[$name] ?? $default;
    }

    public function hasStamp(string $name): bool
    {
        return isset($this->stamps[$name]);
    }

    public function applyStamp(string $name, mixed $value): self
    {
        if (isset($value)) {
            $this->stamps[$name] = $value;
        } else {
            unset($this->stamps[$name]);
        }

        return $this;
    }

    public function applyStamps(Envelope|array $listOfStamps): self
    {
        foreach ($listOfStamps as $name => $value) {
            $this->stamp($name, $value);
        }

        return $this;
    }

    public function removeStamps(...$listOfNames): self
    {
        if (empty($listOfNames)) {
            $this->stamps = [];
            return $this;
        }

        foreach ($listOfNames as $name) {
            if (is_array($name)) {
                $this->removeStamps(...$name);
            } else {
                unset($this->stamps[$name]);
            }
        }

        return $this;
    }

    public function hasStamps(...$listOfNames): bool
    {
        if (empty($listOfNames)) {
            return !empty($this->stamps);
        }

        foreach ($listOfNames as $name) {
            if (!isset($this->stamps[$name])) {
                return false;
            }
        }

        return true;
    }

    public function listOfStamps(bool $withTimestamps = false): array
    {
        $stamps = $this->stamps;
        if (!$withTimestamps) {
            $stamps = array_filter($stamps, fn ($s) => !$s instanceof DateTime);
        }

        return $stamps;
    }

    // TIMESTAMPS

    public function timestamp(string $name, ?DateTime $default = null): ?DateTime
    {
        $stamp = $this->stamp($name, $default);

        return $stamp instanceof DateTime ? $stamp : null;
    }

    public function applyTimestamp(string $name, DateTime|string|null $timestamp = null): self
    {
        if (is_string($timestamp)) {
            $timestamp = $timestamp ? new DateTime($timestamp) : null;
        }

        if (!$timestamp) {
            $timestamp = new DateTime;
        }

        $this->stamps[$name] = $timestamp;

        return $this;
    }

    public function listOfTimestamps(): array
    {
        $stamps = $this->stamps;

        return array_filter($stamps, fn ($s) => $s instanceof DateTime);
    }

    // ARRAY ACCESS

    public function offsetGet(mixed $offset): mixed
    {
        return $this->stamps[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->stamps[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->stamps[$offset]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->stamps[$offset]);
    }

    // PROPERTY ACCESS

    public function __get(string $name): mixed
    {
        return $this->stamps[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->stamps[$name] = $value;
    }

    public function __unset(string $name): void
    {
        unset($this->stamps[$name]);
    }

    public function __isset(string $name): bool
    {
        return isset($this->stamps[$name]);
    }

    // ITERATOR

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->stamps);
    }
}
