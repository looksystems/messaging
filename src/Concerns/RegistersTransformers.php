<?php

namespace Look\Messaging\Concerns;

use Look\Messaging\Mock\MockTransformer;
use Look\Messaging\Support\DedupeUtils;
use Look\Messaging\Support\Wildcard;

trait RegistersTransformers
{
    protected array $transformers = [];

    // RELAYS

    public function transform(array|string $typeOrList, $transformer = null): self
    {
        if (is_array($typeOrList)) {
            foreach ($typeOrList as $key => $value) {
                if ($transformer && is_numeric($key)) {
                    $this->transform($value, $transformer);
                } else {
                    $this->transform($key, $value);
                }
            }
            return $this;
        }

        if (!isset($this->transformers[$typeOrList])) {
            $this->transformers[$typeOrList] = [];
        }

        $this->transformers[$typeOrList][] = ['transformer' => $transformer];

        return $this;
    }

    public function registerTransformers(array $list): self
    {
        $this->transform($list);

        return $this;
    }

    public function listOfTransformers(?string $type = null): array
    {
        if (is_null($type)) {
            $transformers = [];
            foreach ($this->transformers as $list) {
                $transformers = array_merge($transformers, $list);
            }
        } else {
            $transformers = Wildcard::findByKey($type, $this->transformers);
        }

        $transformers = array_map(fn ($item) => $item['transformer'], $transformers);

        return DedupeUtils::list($transformers);
    }

    public function mockTransformer(string $type, $transformer = null): MockTransformer
    {
        $mock = new MockTransformer($transformer);
        $this->transform($type, $mock);

        return $mock;
    }
}
