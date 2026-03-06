<?php

namespace Adeliom\EasyConfigBundle\Tests\Enum;

use Adeliom\EasyConfigBundle\Enum\EasyConfigEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyConfigBundle\Enum\EasyConfigEnum::class)]
final class EasyConfigTypeTest extends TestCase
{
    public function testEnumExposesDeclaredValues(): void
    {
        $values = EasyConfigEnum::getValues();

        self::assertContains(EasyConfigEnum::TEXT->value, $values);
        self::assertContains(EasyConfigEnum::BOOLEAN->value, $values);
        self::assertContains(EasyConfigEnum::DATETIME->value, $values);
    }
}
