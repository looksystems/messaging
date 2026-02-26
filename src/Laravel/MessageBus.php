<?php

namespace Look\Messaging\Laravel;

use Look\Messaging\MessageBus as MessageBusBase;

class MessageBus extends MessageBusBase
{
    // CONFIG

    public function applyConfig(array $config): self
    {
        $this->registerRules($config['rules'] ?? []);
        parent::applyConfig($config);

        return $this;
    }

    public function rules(array|string $typeOrList, array $rules = []): self
    {
        if (is_array($typeOrList)) {
            foreach ($typeOrList as $key => $value) {
                if ($rules && is_numeric($key)) {
                    $this->rules($value, $rules);
                } else {
                    $this->rules($key, $value);
                }
            }
            return $this;

        }

        $this->validate($typeOrList, Rules::make($rules));

        return $this;
    }

    public function registerRules(array $list): self
    {
        return $this->rules($list);
    }
}
