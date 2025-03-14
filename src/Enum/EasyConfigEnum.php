<?php

namespace Adeliom\EasyConfigBundle\Enum;

enum EasyConfigEnum: string
{
    case CODE = 'code';
    case EMAIL = 'email';
    case NUMBER = 'number';
    case JSON = 'json';
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case WYSIWYG = 'wysiwyg';
    case BOOLEAN = 'boolean';
    case IMAGE = 'image';
    case FILE = 'file';
    case COLOR = 'color';
    case DATE = 'date';
    case TIME = 'time';
    case DATETIME = 'datetime';

    public static function getValues(): array
    {
        return array_map(fn (self $value) => $value->value, self::cases());
    }
}
