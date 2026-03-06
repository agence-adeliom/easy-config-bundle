<?php

namespace Adeliom\EasyConfigBundle\Tests\DependencyInjection;

use Adeliom\EasyConfigBundle\DependencyInjection\EasyConfigExtension;
use Adeliom\EasyConfigBundle\Tests\Fixtures\Entity\TestConfig;
use Adeliom\EasyConfigBundle\Tests\Fixtures\Repository\TestConfigRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EasyConfigExtensionTest extends TestCase
{
    public function testExtensionLoadsParametersAndServices(): void
    {
        $container = new ContainerBuilder();
        $extension = new EasyConfigExtension();

        $extension->load([[
            'config_class' => TestConfig::class,
            'config_repository' => TestConfigRepository::class,
        ]], $container);

        self::assertSame('easy_config', $extension->getAlias());
        self::assertSame(TestConfig::class, $container->getParameter('easy_config.config_class'));
        self::assertSame(TestConfigRepository::class, $container->getParameter('easy_config.config_repository'));
        self::assertTrue($container->hasDefinition('easy_config.config_repository'));
        self::assertTrue($container->hasDefinition('Adeliom\\EasyConfigBundle\\Twig\\EasyConfigExtension'));
        self::assertTrue($container->hasAlias('easy_config.twig_extension'));
    }
}
