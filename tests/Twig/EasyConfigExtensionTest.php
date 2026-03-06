<?php

namespace Adeliom\EasyConfigBundle\Tests\Twig;

use Adeliom\EasyConfigBundle\Entity\Config;
use Adeliom\EasyConfigBundle\Enum\EasyConfigEnum;
use Adeliom\EasyConfigBundle\Repository\ConfigRepository;
use Adeliom\EasyConfigBundle\Twig\EasyConfigExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Markup;
use Twig\TwigFunction;

#[CoversClass(\Adeliom\EasyConfigBundle\Twig\EasyConfigExtension::class)]
final class EasyConfigExtensionTest extends TestCase
{
    public function testTwigExtensionRegistersEasyConfigFunction(): void
    {
        $repository = $this->createMock(ConfigRepository::class);
        $extension = new EasyConfigExtension($repository);

        self::assertContainsOnlyInstancesOf(TwigFunction::class, $extension->getFunctions());
        self::assertSame('easy_config', $extension->getFunctions()[0]->getName());
    }

    public function testGetConfigReturnsMarkupForTextTypes(): void
    {
        $config = (new Config())
            ->setType(EasyConfigEnum::TEXT->value)
            ->setValue('<b>hello</b>');

        $repository = $this->createMock(ConfigRepository::class);
        $repository
            ->method('getByKey')
            ->with('homepage_title')
            ->willReturn($config);

        $extension = new EasyConfigExtension($repository);
        $result = $extension->getConfig($this->createMock(Environment::class), [], 'homepage_title');

        self::assertInstanceOf(Markup::class, $result);
        self::assertSame('<b>hello</b>', (string) $result);
    }

    public function testGetConfigReturnsStructuredDataWhenDirectValueIsDisabled(): void
    {
        $config = (new Config())
            ->setType(EasyConfigEnum::TEXT->value)
            ->setValue('plain text');

        $repository = $this->createMock(ConfigRepository::class);
        $repository->method('getByKey')->willReturn($config);

        $extension = new EasyConfigExtension($repository);
        $result = $extension->getConfig($this->createMock(Environment::class), [], 'homepage_title', false);

        self::assertSame([
            'type' => EasyConfigEnum::TEXT->value,
            'value' => 'plain text',
            'raw_value' => 'plain text',
        ], $result);
    }

    public function testGetConfigDecodesJsonValues(): void
    {
        $config = (new Config())
            ->setType(EasyConfigEnum::JSON->value)
            ->setValue('{"enabled":true}');

        $repository = $this->createMock(ConfigRepository::class);
        $repository->method('getByKey')->willReturn($config);

        $extension = new EasyConfigExtension($repository);

        self::assertSame(['enabled' => true], $extension->getConfig($this->createMock(Environment::class), [], 'feature_flags'));
    }
}
