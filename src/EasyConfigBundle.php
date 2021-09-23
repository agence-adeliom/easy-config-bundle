<?php

namespace Adeliom\EasyConfigBundle;

use Adeliom\EasyConfigBundle\DependencyInjection\EasyConfigExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyConfigBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new EasyConfigExtension();
    }
}
