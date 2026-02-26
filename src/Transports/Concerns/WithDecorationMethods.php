<?php

namespace Look\Messaging\Transports\Concerns;

use Closure;
use Look\Messaging\Contracts\Decorator;
use Look\Messaging\Decorators\DefaultDecorator;
use Look\Messaging\Decorators\StandardDecorator;

trait WithDecorationMethods
{
    protected Decorator|Closure|array|null $decorateUsing = null;

    public function decorate(object $data): object
    {
        if ($this->decorateUsing) {
            if ($this->decorateUsing instanceof Decorator) {
                return $this->decorateUsing->decorate($data);
            }

            if ($this->decorateUsing instanceof Closure) {
                return call_user_func($this->decorateUsing, $data);
            }

            return StandardDecorator::apply($data, $this->decorateUsing);
        }

        return DefaultDecorator::decorate($data);
    }

    public function decorateUsing(Decorator|Closure|array $callback): self
    {
        $this->decorateUsing = $callback;

        return $this;
    }
}
