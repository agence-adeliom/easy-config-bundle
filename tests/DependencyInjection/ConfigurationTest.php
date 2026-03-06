<?php

namespace Adeliom\EasyConfigBundle\Tests\DependencyInjection;

use Adeliom\EasyConfigBundle\DependencyInjection\Configuration;
use Adeliom\EasyConfigBundle\Tests\Fixtures\Entity\TestConfig;
use Adeliom\EasyConfigBundle\Tests\Fixtures\Repository\TestConfigRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testConfigurationAcceptsValidClasses(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[
            'config_class' => TestConfig::class,
            'config_repository' => TestConfigRepository::class,
        ]]);

        self::assertSame(TestConfig::class, $config['config_class']);
        self::assertSame(TestConfigRepository::class, $config['config_repository']);
    }

    public function testConfigurationRejectsInvalidConfigClass(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        (new Processor())->processConfiguration(new Configuration(), [[
            'config_class' => \stdClass::class,
        ]]);
    }
}
