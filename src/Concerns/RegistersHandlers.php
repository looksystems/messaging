<?php

namespace Look\Messaging\Concerns;

use Look\Messaging\Mock\MockHandler;
use Look\Messaging\Support\DedupeUtils;
use Look\Messaging\Support\Wildcard;

trait RegistersHandlers
{
    protected array $handlers = [];

    // HANDLERS

    public function handle(array|string $typeOrList, $handler = null, bool $fallback = false): self
    {
        if (is_array($typeOrList)) {
            foreach ($typeOrList as $key => $value) {
                if ($handler && is_numeric($key)) {
                    $this->handle($value, $handler, $fallback);
                } else {
                    $this->handle($key, $value, $fallback);
                }
            }
            return $this;
        }

        if (str_ends_with($typeOrList, ':fallback')) {
            $typeOrList = substr($typeOrList, 0, -9);
            $fallback = true;
        }

        if (!isset($this->handlers[$typeOrList])) {
            $this->handlers[$typeOrList] = [];
        }

        $this->handlers[$typeOrList][] = ['handler' => $handler, 'fallback' => $fallback];

        return $this;
    }

    public function registerHandlers(array $list, bool $fallback = false): self
    {
        $this->handle($list, fallback: $fallback);

        return $this;
    }

    public function listOfHandlers(?string $type = null, ?bool $fallback = null): array
    {
        if (is_null($type)) {
            $handlers = [];
            foreach ($this->handlers as $list) {
                $handlers = array_merge($handlers, $list);
            }
        } else {
            $handlers = Wildcard::findByKey($type, $this->handlers);
        }

        if (isset($fallback)) {
            $handlers = array_filter($handlers, function ($item) use ($fallback) {
                return $item['fallback'] === $fallback;
            });
        }

        $handlers = array_map(fn ($item) => $item['handler'], $handlers);

        return DedupeUtils::list($handlers);
    }

    public function mockHandler(string $type, $handler = null, bool $fallback = false): MockHandler
    {
        $mock = new MockHandler($handler);
        $this->handle($type, $mock, $fallback);

        return $mock;
    }
}
