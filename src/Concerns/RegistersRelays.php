<?php

namespace Look\Messaging\Concerns;

use Look\Messaging\Mock\MockRelay;
use Look\Messaging\Support\DedupeUtils;
use Look\Messaging\Support\Wildcard;

trait RegistersRelays
{
    protected array $relays = [];

    // RELAYS

    public function relay(array|string $typeOrList, $relay = null, bool $fallback = false): self
    {
        if (is_array($typeOrList)) {
            foreach ($typeOrList as $key => $value) {
                if ($relay && is_numeric($key)) {
                    $this->relay($value, $relay, $fallback);
                } else {
                    $this->relay($key, $value, $fallback);
                }
            }
            return $this;
        }

        if (str_ends_with($typeOrList, ':fallback')) {
            $typeOrList = substr($typeOrList, 0, -9);
            $fallback = true;
        }

        if (!isset($this->relays[$typeOrList])) {
            $this->relays[$typeOrList] = [];
        }

        $this->relays[$typeOrList][] = ['relay' => $relay, 'fallback' => $fallback];

        return $this;
    }

    public function registerRelays(array $list, bool $fallback = false): self
    {
        $this->relay($list, fallback: $fallback);

        return $this;
    }

    public function listOfRelays(?string $type = null, ?bool $fallback = null): array
    {
        if (is_null($type)) {
            $relays = [];
            foreach ($this->relays as $list) {
                $relays = array_merge($relays, $list);
            }
        } else {
            $relays = Wildcard::findByKey($type, $this->relays);
        }

        if (isset($fallback)) {
            $relays = array_filter($relays, function ($item) use ($fallback) {
                return $item['fallback'] === $fallback;
            });
        }

        $relays = array_map(fn ($item) => $item['relay'], $relays);

        return DedupeUtils::list($relays);
    }

    public function mockRelay(string $type, $relay = null, bool $fallback = false): MockRelay
    {
        $mock = new MockRelay($relay);
        $this->relay($type, $mock, $fallback);

        return $mock;
    }
}
