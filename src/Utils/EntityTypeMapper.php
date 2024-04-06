<?php

namespace App\Utils;

class EntityTypeMapper
{
    public static function getNameFromClass(string $class): string
    {
        return basename(str_replace("\\", "/", $class));
    }
}
