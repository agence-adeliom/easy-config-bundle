<?php

namespace Adeliom\EasyConfigBundle;

use Adeliom\EasyConfigBundle\DependencyInjection\EasyConfigExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyConfigBundle extends Bundle
{
    /**
     * @return ExtensionInterface|null The container extension
     */
    public function getContainerExtension()
    {
        return new EasyConfigExtension();
    }
}
