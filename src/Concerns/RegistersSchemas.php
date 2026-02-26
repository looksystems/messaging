<?php

namespace Look\Messaging\Concerns;

use Look\Messaging\Exceptions\NoSchemaException;
use Look\Messaging\Validators\JsonSchema;

trait RegistersSchemas
{
    /**
     * @throws NoSchemaException
     */
    public function schema(string $type, array|string $idOrSchema, ?string $schema = null): self
    {
        if (is_array($idOrSchema)) {
            $jsonSchema = JsonSchema::make($idOrSchema);
        } elseif (strncasecmp($schema, 'dir:', 4) === 0) {
            $jsonSchema = JsonSchema::prefix($idOrSchema, substr($schema, 4));
        } elseif (strncasecmp($schema, 'file:', 5) === 0) {
            $jsonSchema = JsonSchema::file($idOrSchema, substr($schema, 5));
        } elseif ($schema) {
            $jsonSchema = JsonSchema::raw($idOrSchema, $schema);
        } else {
            throw new NoSchemaException('No schema');
        }

        return $this->validate($type, $jsonSchema);
    }

    public function schemas(array|string $typeOrList, array $schema = []): self
    {
        if (is_array($typeOrList)) {
            foreach ($typeOrList as $key => $value) {
                if ($schema && is_numeric($key)) {
                    $this->validate($value, JsonSchema::make($schema));
                } else {
                    $this->validate($key, JsonSchema::make($value));
                }
            }

            return $this;
        }

        $this->validate($typeOrList, JsonSchema::make($schema));

        return $this;
    }

    public function registerSchemas(array $list): self
    {
        $this->schemas($list);

        return $this;
    }
}
