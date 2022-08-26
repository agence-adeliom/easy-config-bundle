<?php

namespace Adeliom\EasyConfigBundle;

use Adeliom\EasyConfigBundle\DependencyInjection\EasyConfigExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyConfigBundle extends Bundle
{
    /**
     * @return ExtensionInterface|null The container extension
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new EasyConfigExtension();
    }
}
