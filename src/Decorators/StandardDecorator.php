<?php

namespace Look\Messaging\Decorators;

use Closure;
use Look\Messaging\Contracts\Decorator;

class StandardDecorator implements Decorator
{
    protected array $decorations = [];
    protected bool $preserve = false;

    // INSTANTIATION

    public static function apply(object $data, array $decorations = [], bool $preserve = false): object
    {
        return (new self($decorations, $preserve))->decorate($data);
    }

    public static function make(array $decorations = [], bool $preserve = false)
    {
        return new self($decorations, $preserve);
    }

    public function __construct(array $decorations = [], bool $preserve = false)
    {
        if ($decorations) {
            $this->add($decorations);
        }

        $this->preserve = $preserve;
    }

    public function add(array|string $nameOrList, $value = null): self
    {
        if (is_array($nameOrList)) {
            foreach ($nameOrList as $key => $value) {
                if (is_numeric($key)) {
                    $this->add($value);
                } elseif (isset($value)) {
                    $this->add($key, $value);
                }
            }

            return $this;
        }

        if (isset($value)) {
            $this->decorations[$nameOrList] = $value;
        } elseif (
            str_starts_with($nameOrList, 'payload:')
            || str_starts_with($nameOrList, 'envelope:')
        ) {
            $index = array_search($nameOrList, $this->decorations);
            if ($index === false) {
                $this->decorations[] = $nameOrList;
            }
        }

        return $this;
    }

    public function remove(array|string $nameOrList): self
    {
        if (is_array($nameOrList)) {
            foreach ($nameOrList as $key) {
                $this->remove($key);
            }

            return $this;
        }

        $index = array_search($nameOrList, $this->decorations);
        if ($index !== false) {
            unset($this->decorations[$index]);
        }

        unset($this->decorations[$nameOrList]);

        return $this;
    }

    public function preserve(bool $state = true): self
    {
        $this->preserve = $state;

        return $this;
    }

    // DECORATOR

    public function decorate(object $data): object
    {
        if (empty($this->decorations)) {
            return $data;
        }

        $data = clone $data;

        foreach ($this->decorations as $key => $value) {
            [$key, $value] = $this->resolveKeyValue($key, $value, $data);

            if (is_numeric($key)) {
                continue;
            }

            if ($value instanceof Closure) {
                $value = call_user_func($value, $data, $key);
            }

            if (is_null($value)) {
                continue;
            }

            if (!$this->preserve || !isset($data->$key)) {
                $data->$key = $value;
            }
        }

        return $data;
    }

    protected function resolveKeyValue(string|int $key, mixed $value, object &$data): array
    {
        if (!is_string($value)) {
            return [$key, $value];
        }

        // use payload value
        if (str_starts_with($value, 'payload:')) {
            if (is_numeric($key)) {
                $key = substr($value, 8);
            }
            if (isset($data->payload->$key)) {
                $value = $data->payload->$key;
            }
            // use envelope value
        } elseif (str_starts_with($value, 'envelope:')) {
            if (is_numeric($key)) {
                $key = substr($value, 9);
            }
            if (isset($data->envelope[$key])) {
                $value = $data->envelope[$key];
            }
            // treat as envelope value (when no key provided)
        } elseif (
            is_numeric($key)
            && isset($data->envelope[$value])
        ) {
            $key = $value;
            $value = $data->envelope[$value];
        }

        return [$key, $value];
    }
}
