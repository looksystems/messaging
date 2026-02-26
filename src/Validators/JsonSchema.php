<?php

namespace Look\Messaging\Validators;

use Closure;
use Exception;
use Look\Messaging\Contracts\Validator;
use InvalidArgumentException;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator as OpisValidator;

class JsonSchema implements Validator
{
    protected OpisValidator $validator;
    protected string $schemaPrefix = '';
    protected ?Closure $resolveSchemaUsing = null;

    // INSTANTIATION

    public static function make(array $schema): self
    {
        $id = $schema['id'] ?? null;
        if (!$id) {
            throw new InvalidArgumentException('No schema id');
        }

        $path = $schema['path'] ?? null;
        if ($path) {
            $prefix = $schema['prefix'] ?? null;
            if ($prefix) {
                return self::prefix($prefix, $path);
            }
            return self::file($id, $path);
        }

        $raw = $schema['schema'] ?? null;
        if (!isset($raw)) {
            throw new InvalidArgumentException('Invalid schema');
        }

        return self::raw($id, $raw);
    }

    public static function raw(string $id, string|object|bool $schema): self
    {
        $validator = new OpisValidator;
        $validator->resolver()->registerRaw($schema, $id);

        return new self($validator);
    }

    public static function file(string $id, string $filepath): self
    {
        $validator = new OpisValidator;
        $validator->resolver()->registerFile($id, $filepath);

        return new self($validator);
    }

    public static function prefix(string $prefix, string $dirpath): self
    {
        $validator = new OpisValidator;
        $validator->resolver()->registerPrefix($prefix, $dirpath);

        return (new self($validator))
            ->setSchemaPrefix($prefix);
    }

    protected function __construct(OpisValidator $validator)
    {
        $this->validator = $validator;
    }

    // SCHEMA

    public function getSchemaIdFromType(string $type): ?string
    {
        return $this->schemaPrefix(
            str_replace('.', '/', $type).'.json'
        );
    }

    public function setSchemaPrefix(string $prefix): self
    {
        $this->schemaPrefix = $prefix;

        return $this;
    }

    public function schemaPrefix(?string $postfix = null): string
    {
        if ($postfix) {
            return $this->schemaPrefix.ltrim($postfix, '/');
        }

        return $this->schemaPrefix;
    }

    public function resolveSchemaUsing($resolveSchemaUsing): self
    {
        $this->resolveSchemaUsing = $resolveSchemaUsing;

        return $this;
    }

    // VALIDATOR

    public function validate(object $message, string $type): ValidationResult
    {
        $id = $this->getSchemaIdFromType($type);
        if (!$id) {
            return ValidationResult::noSchema();
        }

        try {
            $result = $this->validator->validate($message, $id);
        } catch (Exception $e) {
            $log = $e->getMessage();
            if (stripos($log, 'Schema not found') !== false) {
                return ValidationResult::noSchema();
            }

            return ValidationResult::invalidSchema($e->getMessage());
        }

        if (!$result->isValid()) {
            return ValidationResult::invalidMessage(
                (string) $result,
                (new ErrorFormatter)->format($result->error())
            );
        }

        return ValidationResult::valid();
    }
}
