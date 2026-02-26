<?php

namespace Look\Messaging\Concerns;

use Closure;
use Look\Messaging\Contracts\Decorator;
use Look\Messaging\Contracts\Serializer;
use Look\Messaging\Decorators\DefaultDecorator;
use Look\Messaging\Serializers\DefaultSerializer;

trait AppliesConfig
{
    // CONFIG

    public function applyConfig(array $config): self
    {
        if (!empty($config['decorate'])) {
            $this->initDecorator($config['decorate']);
        }

        if (!empty($config['serialize'])) {
            $this->initSerializer($config['serialize']);
        }

        $this
            ->registerTransformers($config['transformers'] ?? [])
            ->registerSchemas($config['schemas'] ?? [])
            ->registerValidators($config['validators'] ?? [])
            ->registerHandlers($config['handlers'] ?? [])
            ->registerRelays($config['relays'] ?? [])
            ->registerTransports($config['transports'] ?? []);

        return $this;
    }

    protected function initDecorator(Decorator|Closure|array|string $decorator)
    {
        if (is_string($decorator)) {
            $class = $decorator;
            $decorator = function () use ($class) {
                return $this->getContainer()->get($class);
            };
        }

        DefaultDecorator::init($decorator);
    }

    protected function initSerializer(Serializer|Closure|string $serializer)
    {
        if (is_string($serializer)) {
            $class = $serializer;
            $serializer = function () use ($class) {
                return $this->getContainer()->get($class);
            };
        }

        DefaultSerializer::init($serializer);
    }
}
