<?php

namespace Look\Messaging\Middleware;

use Look\Messaging\Contracts\Validator;
use Look\Messaging\Exceptions\MessageBusException;
use Look\Messaging\Support\DedupeSession;
use Look\Messaging\Validators\ValidationAction;
use Look\Messaging\Validators\ValidationResult;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ValidateMessage extends Middleware
{
    /**
     * @throws MessageBusException
     */
    public function handle($message)
    {
        $type = $message->type();
        if (!$type) {
            $result = ValidationResult::noSchema();
            $action = $this->bus->actions()->whenNoSchema;
        } else {
            [$result, $action] = $this->call(
                $this->bus->listOfValidators($type, fallback: false, asArray: true),
                $message,
                $type
            );

            if (
                $result->isNoSchema()
                && $action === ValidationAction::Allow
            ) {
                [$result, $action] = $this->call(
                    $this->bus->listOfValidators($type, fallback: true, asArray: true),
                    $message,
                    $type
                );
            }
        }

        if ($result->isValid()) {
            return $this->next($message);
        }

        if ($action === ValidationAction::Exception) {
            throw $result->getException();
        }

        if ($action === ValidationAction::Drop) {
            $this->state->abort();
        }

        return $this->next($message);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function call(array $validators, object $message, string $type): array
    {
        $result = ValidationResult::noSchema();
        $action = $this->bus->actions()->whenNoSchema;

        $dedupe = new DedupeSession;
        foreach ($validators as $definition) {
            $validator = $definition['validator'];
            if ($dedupe->called($validator)) {
                continue;
            }

            if (is_string($validator)) {
                $validator = $this->container()->get($validator);
            }

            $context = [
                'type' => $type,
                'message' => $message,
                'state' => $this->state,
                'bus' => $this->bus,
            ];

            if ($validator instanceof ValidationResult) {
                $result = $validator;
            } elseif ($validator instanceof Validator) {
                $result = $this->container()->call([$validator, 'validate'], $context);
            } elseif (is_callable($validator)) {
                $result = ValidationResult::make(
                    $this->container()->call($validator, $context)
                );
            } else {
                $result = ValidationResult::invalidSchema('Invalid validator');
            }

            if ($result->isValid()) {
                $action = null;
                continue;
            }

            $actions = $definition['actions'] ?? $this->bus->actions();
            $action = $actions->getActionForResult($result);

            if ($action !== ValidationAction::Allow) {
                break;
            }
        }

        return [$result, $action];
    }
}
