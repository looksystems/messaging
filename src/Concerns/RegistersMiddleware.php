<?php

namespace Look\Messaging\Concerns;

use Look\Messaging\Middleware\Middleware;

trait RegistersMiddleware
{
    protected array $middleware = [];

    // MIDDLEWARE

    public function middleware(Middleware|array $middlewareOrList, bool $replace = false): self
    {
        if ($replace) {
            $this->middleware = [];
        }

        $this->appendMiddleware($middlewareOrList);

        return $this;
    }

    public function replaceMiddleware(Middleware|array $middlewareOrList): self
    {
        return $this->middleware($middlewareOrList, replace: true);
    }

    public function prependMiddleware(Middleware|array $middlewareOrList): self
    {
        if (!is_array($middlewareOrList)) {
            $middlewareOrList = [$middlewareOrList];
        }

        $this->middleware = array_merge($middlewareOrList, $this->middleware);

        return $this;

    }

    public function appendMiddleware(Middleware|array $middlewareOrList): self
    {
        if (!is_array($middlewareOrList)) {
            $middlewareOrList = [$middlewareOrList];
        }

        $this->middleware = array_merge($this->middleware, $middlewareOrList);

        return $this;
    }

    public function dropMiddleware(Middleware|array|string $middlewareOrList): self
    {
        if (is_array($middlewareOrList)) {
            foreach ($middlewareOrList as $middleware) {
                $this->dropMiddleware($middleware);
            }
            return $this;
        }

        if (is_string($middlewareOrList)) {
            $this->middleware = array_filter(
                $this->middleware,
                function ($item) use ($middlewareOrList) {
                    return get_class($item) !== $middlewareOrList;
                }
            );
        } else {
            $this->middleware = array_filter(
                $this->middleware,
                function ($item) use ($middlewareOrList) {
                    return $item !== $middlewareOrList;
                }
            );
        }

        return $this;
    }

    public function listOfMiddleware(): array
    {
        return $this->middleware;
    }
}
