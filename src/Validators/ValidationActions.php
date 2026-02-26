<?php

namespace Look\Messaging\Validators;

class ValidationActions
{
    public static function default(): ValidationActions
    {
        return new self(
            ValidationAction::Allow,
            ValidationAction::Exception,
            ValidationAction::Exception
        );
    }

    public static function throw(): ValidationActions
    {
        return new self(
            ValidationAction::Exception,
            ValidationAction::Exception,
            ValidationAction::Exception
        );
    }

    public static function drop(): ValidationActions
    {
        return new self(
            ValidationAction::Drop,
            ValidationAction::Drop,
            ValidationAction::Drop
        );
    }

    public static function allow(): ValidationActions
    {
        return new self(
            ValidationAction::Allow,
            ValidationAction::Allow,
            ValidationAction::Allow
        );
    }

    public function __construct(
        public ?ValidationAction $whenNoSchema = null,
        public ?ValidationAction $whenInvalidMessage = null,
        public ?ValidationAction $whenInvalidSchema = null
    ) {}

    public function getActionForResult(ValidationResult $result): ?ValidationAction
    {
        $when = 'when'.ucfirst($result->type());

        return $this->$when ?? null;
    }
}
