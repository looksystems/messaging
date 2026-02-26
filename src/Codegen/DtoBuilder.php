<?php

namespace Look\Messaging\Codegen;

use Look\Messaging\Support\Str;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

/**
 * @see https://github.com/nette/php-generator
 * @see https://github.com/Galbar/JsonPath-PHP
 */
class DtoBuilder
{
    protected array $output = [];
    protected ?string $domain = null;
    protected ?string $namespace = null;
    protected string $classPostfix = 'Event';

    public static function fromSchema(object $schema): self
    {
        return (new static)->addSchema($schema);
    }

    public function addSchema(object $schema): self
    {
        $this->output = array_merge(
            $this->output,
            $this->processSchema(
                $schema,
                namespace: $this->namespace,
                postfix: $this->classPostfix
            )
        );

        return $this;
    }

    public function output(): array
    {
        return $this->output;
    }

    // TODO: dto build is a quick hack/script to learn the requirements better
    protected function processSchema(
        object $schema,
        ?string $name = null,
        ?string $namespace = null,
        ?string $uri = null,
        ?string $postfix = null,
    ) {
        $output = [];

        if (property_exists($schema, '$id')) {
            $uri = $schema->{'$id'};
            $path = parse_url($schema->{'$id'}, PHP_URL_PATH);
            $info = pathinfo($path);
            $className = Str::studly($info['filename'].$postfix);
            if ($info['dirname']) {
                $parts = array_map(
                    fn ($word) => Str::studly($word),
                    array_filter(explode('/', $info['dirname']))
                );
                $namespace = ($namespace ? $namespace.'\\' : '').implode('\\', $parts);
                $namespace = rtrim($namespace, '\\');
                $fullClassName = $namespace.'\\'.$className;
            }
        } else {
            $className = Str::studly($name);
            $fullClassName = $namespace.'\\'.$className;
        }

        if (!$className) {
            return [];
        }

        $filename = trim(
            str_replace(
                [$this->namespace, '\\'],
                ['', '/'],
                $fullClassName
            ),
            '/'
        ).'.php';

        if ($namespace) {
            $root = new PhpNamespace($namespace);
            $class = $root->addClass($className);
        } else {
            $root = new ClassType($className);
            $class = $root;
        }

        $output[] = [
            'code' => $root,
            'class' => $fullClassName,
            'filename' => $filename,
            'uri' => $uri,
        ];

        // $class->addImplement(Message::class);

        $class->addComment($uri);
        if (!empty($schema->title)) {
            $class->addComment($schema->title);
        }

        $required = $schema->required ?? [];
        foreach ($schema->properties ?? [] as $propertyName => $definition) {
            $type = $definition->type ?? null;
            if (!$type) {
                continue;
            }

            if ($type === 'object') {
                $type = Str::studly($propertyName);
                $subspace = $namespace ? $namespace.'\\'.$className : $className;
                $fulltype = '\\'.$subspace.'\\'.$type;
                $suburi = $uri;
                if (!str_contains($uri, '#')) {
                    $suburi .= '#';
                } else {
                    $suburi .= '/';
                }
                $suburi .= $propertyName;
                $output = array_merge(
                    $output,
                    $this->processSchema($definition, $type, $subspace, $suburi)
                );
                $root->addUse($fulltype);
                $type = $fulltype;

                if (in_array($propertyName, $required)) {
                    $init[] = '$this->'.$propertyName.' = new '.$type.';';
                }

                // need to support patternProperties and additionalProperties
            }

            if ($type === 'array') {
                // need to support arrays including items and additionalItems
            }

            $nullable = null;
            if (is_array($type)) {
                if (count($type) === 2) {
                    $key = array_search('null', $type);
                    if ($key !== false) {
                        unset($type[$key]);
                        $nullable = '?';
                    }
                }

                $type = implode('|', $type);
            }

            $type = str_replace(
                ['boolean', 'integer', 'number'],
                ['bool', 'int', 'float'],
                $type
            );

            $default = $definition->default ?? null;

            $isConst = property_exists($definition, 'const');
            if ($isConst) {
                $default = $definition->const;
            }

            if (is_null($default)) {
                $property = $class->addProperty($propertyName);
            } else {
                $property = $class->addProperty($propertyName, $default);
            }

            if ($type !== 'null') {
                $property->setType($nullable.$type);
            }
            if ($isConst) {
                $property->setReadOnly();
            }
            if (!empty($definition->description)) {
                $property->addComment($definition->description);
            }
            // if (!empty($definition->format)) {
            //	$property->addAttribute('format("'.$definition->format.'"")');
            // }
            if (in_array($propertyName, $required)) {
                $property->addAttribute('required');
            }
        }

        if (!empty($init)) {
            $method = $class->addMethod('__construct')
                ->setBody(implode("\n", $init));

        }

        return $output;
    }

    //
}
