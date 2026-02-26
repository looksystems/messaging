<?php

namespace Look\Messaging\Concerns;

use Psr\Container\ContainerInterface;

trait UsesContainer
{
    // CONTAINER

    public function setContainer(ContainerInterface $container): self
    {
        $this->pipeline->setContainer($container);

        return $this;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->pipeline->getContainer();
    }
}
