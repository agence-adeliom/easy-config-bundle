<?php

namespace Adeliom\EasyConfigBundle\Tests\Entity;

use Adeliom\EasyConfigBundle\Entity\Config;
use Adeliom\EasyConfigBundle\Enum\EasyConfigEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyConfigBundle\Entity\Config::class)]
final class ConfigTest extends TestCase
{
    public function testBooleanAccessorCastsStoredValue(): void
    {
        $config = (new Config())
            ->setType(EasyConfigEnum::BOOLEAN->value)
            ->setValue('1');

        self::assertTrue($config->getBoolean());
        self::assertTrue(isset($config->boolean));
        self::assertTrue($config->boolean);
    }

    public function testDateAndDatetimeMutatorsFormatStoredValue(): void
    {
        $config = (new Config())->setType(EasyConfigEnum::DATE->value);
        $config->setDate(new \DateTime('2026-03-05'));

        self::assertSame('2026-03-05', $config->getValue());
        self::assertSame('2026-03-05', $config->getDate()?->format('Y-m-d'));

        $config->setType(EasyConfigEnum::DATETIME->value);
        $config->setDatetime(new \DateTime('2026-03-05 10:11:12'));

        self::assertSame('2026-03-05 10:11:12', $config->getValue());
        self::assertSame('2026-03-05 10:11:12', $config->getDatetime()?->format('Y-m-d H:i:s'));
    }

    public function testDynamicSetterOnlyWritesMatchingType(): void
    {
        $config = (new Config())->setType(EasyConfigEnum::TEXT->value);

        $config->text = 'hello';
        $config->boolean = 'ignored';

        self::assertSame('hello', $config->getValue());
        self::assertSame('hello', $config->text);
        self::assertNull($config->boolean);
    }
}
