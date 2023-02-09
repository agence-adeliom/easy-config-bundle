<?php

namespace Adeliom\EasyConfigBundle\Enum;

use ReflectionClass;

//@TODO switch to enum after php 8.1 migration
class EasyConfigType
{
    public const CODE = 'code';
    public const EMAIL = 'email';
    public const NUMBER = 'number';
    public const JSON = 'json';
    public const TEXT = 'text';
    public const TEXTAREA = 'textarea';
    public const WYSIWYG = 'wysiwyg';
    public const BOOLEAN = 'boolean';
    public const IMAGE = 'image';
    public const FILE = 'file';
    public const COLOR = 'color';
    public const DATE = 'date';
    public const TIME = 'time';
    public const DATETIME = 'datetime';

    static function getValues(): array
    {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
