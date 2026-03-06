<?php

namespace Adeliom\EasyConfigBundle\Tests\Entity;

use Adeliom\EasyConfigBundle\Entity\Config;
use Adeliom\EasyConfigBundle\Enum\EasyConfigEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyConfigBundle\Entity\Config::class)]
final class ConfigAdditionalTest extends TestCase
{
    public function testCoreAccessorsReturnAssignedValues(): void
    {
        $config = (new Config())
            ->setKey('homepage_title')
            ->setName('Homepage title')
            ->setDescription('Used on the homepage hero')
            ->setType(EasyConfigEnum::TEXT->value)
            ->setValue('Hello');

        self::assertSame('homepage_title', $config->getKey());
        self::assertSame('Homepage title', $config->getName());
        self::assertSame('Used on the homepage hero', $config->getDescription());
        self::assertSame(EasyConfigEnum::TEXT->value, $config->getType());
        self::assertSame('Hello', $config->getValue());
    }

    public function testTimeAndDateAccessorsHandleValidAndInvalidValues(): void
    {
        $config = (new Config())->setType(EasyConfigEnum::TIME->value);
        $config->setTime(new \DateTime('14:15:16'));

        self::assertSame('14:15:16', $config->getValue());
        self::assertSame('14:15:16', $config->getTime()?->format('H:i:s'));

        $config->setType(EasyConfigEnum::DATE->value)->setValue('not-a-date');
        self::assertNull($config->getDate());

        $config->setType(EasyConfigEnum::DATETIME->value)->setValue('not-a-datetime');
        self::assertNull($config->getDatetime());
    }

    public function testMagicAccessorsHandleMatchingAndNonMatchingTypes(): void
    {
        $config = (new Config())->setType(EasyConfigEnum::TIME->value);
        $config->time = '07:08:09';
        $config->text = 'ignored';

        self::assertTrue(isset($config->time));
        self::assertFalse(isset($config->anything));
        self::assertSame('07:08:09', $config->getValue());
        self::assertSame('07:08:09', $config->time?->format('H:i:s'));
        self::assertNull($config->text);
        self::assertNull($config->getBoolean());
        self::assertNull($config->setDate(new \DateTime('2026-03-05')));
        self::assertNull($config->setDatetime(new \DateTime('2026-03-05 10:11:12')));
    }
}
