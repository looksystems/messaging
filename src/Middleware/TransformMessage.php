<?php

namespace Look\Messaging\Middleware;

use Look\Messaging\Contracts\Transformer;

class TransformMessage extends Middleware
{
    public function handle($message)
    {
        $type = $message->type();
        if (!$type) {
            return $this->next($message);
        }

        $transformers = $this->bus->listOfTransformers($type);
        foreach ($transformers as $transformer) {
            if (is_string($transformer)) {
                $transformer = $this->container()->get($transformer);
            }

            $context = [
                'type' => $type,
                'message' => $message,
                'state' => $this->state,
                'bus' => $this->bus,
            ];

            if ($transformer instanceof Transformer) {
                $message = $this->container()->call([$transformer, 'transform'], $context);
            } elseif (is_callable($transformer)) {
                $message = $this->container()->call($transformer, $context);
            }
        }

        return $this->next($message);
    }
}
