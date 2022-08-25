<?php

namespace Adeliom\EasyConfigBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

trait EasyConfigTrait
{
    public function configMenuEntry(): iterable
    {
        $parameterBag = $this->container->get("parameter_bag");
        yield MenuItem::linkToCrud('easy_config.configs', 'fas fa-cogs', $parameterBag->get('easy_config.config_class'));
    }
}
