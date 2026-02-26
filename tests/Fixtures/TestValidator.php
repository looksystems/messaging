<?php

namespace Tests\Fixtures;

use Look\Messaging\Contracts\Validator;
use Look\Messaging\Validators\ValidationResult;

class TestValidator implements Validator
{
    public $message = null;
    public $state = null;
    public int $count = 0;

    public function validate(object $message, string $type): ValidationResult
    {
        $this->message = $message;
        $this->type = $type;
        $this->count++;

        return ValidationResult::valid();
    }
}
