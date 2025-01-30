<?php

namespace App\Domain\DataTable\Service;

use App\Domain\DataTable\Entity\TableField;
use App\Domain\DataTable\TableFieldType;
use const App\Domain\DataTable\TABLE_FIELD_SELECT_COLORS;

class TableFieldTypeService
{
    public static function isValueValid(TableField $field, string $value): bool
    {
        if ($field->getType() === TableFieldType::BooleanType) return in_array($value, ["true", "false"], true);
        elseif (empty($value)) return true; // everything nullable if not boolean
        elseif ($field->getType() === TableFieldType::NumberType) return is_numeric($value);
        else return true;
    }

    public static function isDefaultEmptyValue(TableField $field, string $value): bool
    {
        if ($field->getType() === TableFieldType::BooleanType) return $value === "false";
        elseif (empty($value)) return true;
        else return false;
    }

    public static function getDefaultEmptyValue(TableField $field): string
    {
        if ($field->getType() === TableFieldType::BooleanType) return "false";
        else return "";
    }

    public static function isValidOptionsArray(array $options): bool {
        if (count($options) > 32) return false;

        foreach ($options as $key => $value) {
            if (!is_string($key) || strlen($key) < 1 || strlen($key) > 32) return false;
            if (!in_array($value, TABLE_FIELD_SELECT_COLORS)) return false;
        }

        return true;
    }
}
