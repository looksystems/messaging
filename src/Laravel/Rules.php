<?php

namespace Look\Messaging\Laravel;

use Exception;
use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\Contracts\Validator;
use Look\Messaging\Validators\ValidationResult;
use Illuminate\Support\Facades\Validator as LaravelValidator;

class Rules implements Validator
{
    protected array $rules = [];

    public static function make(array $rules): self
    {
        return new self($rules);
    }

    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    public function setRules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(MessageInterface $message, string $type): ValidationResult
    {
        if (!$this->rules) {
            return ValidationResult::noSchema();
        }

        try {
            $validator = LaravelValidator::make((array) $message->payload(), $this->rules);
        } catch (Exception $e) {
            return ValidationResult::invalidSchema($e->getMessage());
        }

        if ($validator->fails()) {
            return ValidationResult::invalidMessage(explanation: $validator->errors()->toArray());

        }

        return ValidationResult::valid();
    }
}
