<?php

namespace Look\Messaging\Concerns;

use Look\Messaging\Mock\MockValidator;
use Look\Messaging\Support\DedupeUtils;
use Look\Messaging\Support\Wildcard;
use Look\Messaging\Validators\ValidationActions;

trait RegistersValidators
{
    protected ?ValidationActions $defaultActions = null;
    protected array $validators = [];

    // HANDLERS

    public function validate(array|string $typeOrList, $validator = null, ?ValidationActions $actions = null, bool $fallback = false): self
    {
        if (is_array($typeOrList)) {
            foreach ($typeOrList as $key => $value) {
                if ($validator && is_numeric($key)) {
                    $this->validate($value, $validator, $actions, $fallback);
                } else {
                    $this->validate($key, $value, $actions, $fallback);
                }
            }
            return $this;
        }

        if (str_ends_with($typeOrList, ':fallback')) {
            $typeOrList = substr($typeOrList, 0, -9);
            $fallback = true;
        }

        if (!isset($this->validators[$typeOrList])) {
            $this->validators[$typeOrList] = [];
        }

        $this->validators[$typeOrList][] = ['validator' => $validator, 'actions' => $actions, 'fallback' => $fallback];

        return $this;
    }

    public function registerValidators(array $list, ?ValidationActions $actions = null, bool $fallback = false): self
    {
        $this->validate($list, null, $actions, $fallback);

        return $this;
    }

    public function listOfValidators(?string $type = null, ?bool $fallback = null, bool $asArray = false): array
    {
        if (is_null($type)) {
            $validators = [];
            foreach ($this->validators as $list) {
                $validators = array_merge($validators, $list);
            }
        } else {
            $validators = Wildcard::findByKey($type, $this->validators);
        }

        if (isset($fallback)) {
            $validators = array_filter($validators, function ($item) use ($fallback) {
                return $item['fallback'] === $fallback;
            });
        }

        if (!$asArray) {
            $validators = array_map(fn ($item) => $item['validator'], $validators);
            return DedupeUtils::list($validators);
        }

        return $validators;
    }

    public function mockValidator(string $type, $validator = null, bool $fallback = false, ?ValidationActions $actions = null): MockValidator
    {
        $mock = new MockValidator($validator);
        $this->validate($type, $mock, $actions, $fallback);

        return $mock;
    }

    public function actions(?ValidationActions $actions = null): ValidationActions|self
    {
        if ($actions) {
            $this->defaultActions = $actions;
            return $this;
        }

        if (!isset($this->defaultActions)) {
            $this->defaultActions = ValidationActions::default();
        }

        return $this->defaultActions;
    }
}
