<?php

namespace Look\Messaging\Laravel\Concerns;

use Look\Messaging\Message;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;

trait Assertions
{
    public function assertDispatched(string $type, ?array $payload = null): void
    {
        $dispatched = $this->getDispatched();

        PHPUnit::assertNotEmpty(
            $dispatched,
            'No messages were dispatched.'
        );

        $dispatched = $dispatched
            ->filter(fn (Message $message) => $message->type() === $type);

        if (is_null($payload) || $dispatched->isEmpty()) {
            PHPUnit::assertTrue(
                $dispatched->isNotEmpty(),
                "No messages were dispatched with type {$type}."
            );

            return;
        }

        $matchedPayload = $this->filterByPayload($dispatched, $payload);

        PHPUnit::assertTrue(
            $matchedPayload->isNotEmpty(),
            "No messages were dispatched with type {$type} and matching payload."
        );
    }

    /**
     * Get all dispatched messages
     */
    private function getDispatched(): Collection
    {
        return collect($this->messageBus->dispatched()->all());
    }

    /**
     * Filter messages by matching payload
     */
    private function filterByPayload(Collection $messages, array $payload): Collection
    {
        return $messages->filter(function (Message $message) use ($payload) {
            $messagePayload = $message->payload();

            // Compare each key and value from the expected payload
            foreach ($payload as $key => $value) {
                // Check if the key exists in the payload (handles both arrays and objects)
                $keyExists = is_array($messagePayload)
                    ? array_key_exists($key, $messagePayload)
                    : property_exists($messagePayload, $key);

                // Get the value (handles both arrays and objects)
                $actualValue = is_array($messagePayload)
                    ? ($keyExists ? $messagePayload[$key] : null)
                    : ($keyExists ? $messagePayload->$key : null);

                // If key doesn't exist or values don't match
                if (!$keyExists || !$this->compareValues($actualValue, $value)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Recursively compare values, handling nested arrays and objects
     */
    private function compareValues($actual, $expected): bool
    {
        // Convert objects to arrays for comparison
        if (is_object($actual)) {
            $actual = (array) $actual;
        }

        if (is_object($expected)) {
            $expected = (array) $expected;
        }

        // If both are arrays, recursively compare them
        if (is_array($actual) && is_array($expected)) {
            // Different number of elements
            if (count($actual) !== count($expected)) {
                return false;
            }

            // Sort both arrays if they are simple indexed arrays
            // (not associative arrays with specific keys)
            if (array_keys($actual) === range(0, count($actual) - 1) &&
                array_keys($expected) === range(0, count($expected) - 1)) {
                sort($actual);
                sort($expected);
            }

            // Compare each key and value
            foreach ($expected as $key => $value) {
                if (!array_key_exists($key, $actual) ||
                    !$this->compareValues($actual[$key], $value)) {
                    return false;
                }
            }

            return true;
        }

        // Direct comparison for non-array values
        return $actual === $expected;
    }

    public function assertNotDispatched(string $type, ?array $payload = null): void
    {
        $dispatched = $this->getDispatchedOfType($type);

        if (is_null($payload) || $dispatched->isEmpty()) {
            PHPUnit::assertTrue(
                $dispatched->isEmpty(),
                "Messages were dispatched with type {$type}."
            );

            return;
        }

        $matchedPayload = $this->filterByPayload($dispatched, $payload);

        PHPUnit::assertTrue(
            $matchedPayload->isEmpty(),
            "Messages were dispatched with type {$type} and matching payload."
        );
    }

    /**
     * Get dispatched messages of a specific type
     */
    private function getDispatchedOfType(string $type): Collection
    {
        return $this->getDispatched()
            ->filter(fn (Message $message) => $message->type() === $type);
    }

    public function assertNothingDispatched(): void
    {
        $dispatched = $this->getDispatched();

        PHPUnit::assertEmpty(
            $dispatched,
            'Messages were dispatched.'
        );
    }
}
