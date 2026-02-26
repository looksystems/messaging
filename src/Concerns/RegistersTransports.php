<?php

namespace Look\Messaging\Concerns;

use Closure;
use Look\Messaging\Contracts\Transport;
use Look\Messaging\Mock\MockTransport;
use Look\Messaging\Support\Str;
use Look\Messaging\Transports\SnsTransport;
use Look\Messaging\Transports\SqsTransport;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait RegistersTransports
{
    protected array $transports = [];

    // TRANSPORT

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function transport(string $name): ?Transport
    {
        $transport = $this->transports[$name] ?? null;
        if ($transport instanceof Transport) {
            return $transport;
        }

        if ($transport instanceof Closure) {
            $transport = $transport($this);
        } elseif (is_string($transport)) {
            if (isset($this->transports[$transport])) {
                return $this->transport($transport);
            } elseif (class_exists($transport)) {
                $transport = $this->getContainer()->get($transport);
            } else {
                $transport = $this->resolveTransportFromString($transport);
            }
        } elseif (is_array($transport)) {
            $transport = $this->buildTransport($transport);
        }

        if ($transport instanceof Transport) {
            $this->transports[$name] = $transport;
        } else {
            $transport = null;
        }

        return $transport;
    }

    protected function resolveTransportFromString(string $definition): ?Transport
    {
        [$type, $args] = Str::nameAndArgs($definition);

        $definition = [
            'type' => $type,
        ];
        if (isset($args)) {
            $definition['args'] = $args;
        }

        return $this->buildTransport($definition);
    }

    public function registerTransport(string $name, $transport): self
    {
        $this->transports[$name] = $transport;

        return $this;
    }

    public function registerTransports(array $transports): self
    {
        foreach ($transports as $name => $transport) {
            $this->transports[$name] = $transport;
        }

        return $this;
    }

    public function listOfTransports(): array
    {
        return $this->transports;
    }

    public function mockTransport(string $name): MockTransport
    {
        $transport = new MockTransport;
        $this->registerTransport($name, $transport);

        return $transport;
    }

    protected function buildTransport(array $definition): ?Transport
    {
        $type = $definition['type'] ?? null;
        if (!$type) {
            return null;
        }

        $buildMethod = 'build'.Str::studly($type).'Transport';
        if (method_exists($this, $buildMethod)) {
            return $this->$buildMethod($definition);
        }

        return null;
    }

    protected function buildSqsTransport(array $definition): ?Transport
    {
        $defaultQueues = $definition['queues'] ?? [];
        $transport = new SqsTransport($definition['args'] ?? $defaultQueues);
        $this->applyStandardDefinitions($transport, $definition);

        return $transport;
    }

    protected function buildSnsTransport(array $definition): ?Transport
    {
        $defaultTopics = $definition['topics'] ?? [];
        $transport = new SnsTransport($definition['args'] ?? $defaultTopics);
        $this->applyStandardDefinitions($transport, $definition);

        return $transport;
    }

    protected function applyStandardDefinitions(Transport $transport, array $definition): void
    {
        $decorator = $definition['decorate'] ?? null;
        if ($decorator) {
            if (is_string($decorator)) {
                $decorator = $this->getContainer()->get($decorator);
            }
            $transport->decorateUsing($decorator);
        }

        $serializer = $definition['serialize'] ?? null;
        if ($serializer) {
            if (is_string($serializer)) {
                $serializer = $this->getContainer()->get($serializer);
            }
            $transport->serializeUsing($serializer);
        }

        $unserializer = $definition['unserialize'] ?? null;
        if ($unserializer) {
            if (is_string($unserializer)) {
                $unserializer = $this->getContainer()->get($unserializer);
            }
            $transport->unserializeUsing($unserializer);
        }
    }
}
