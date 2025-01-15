<?php

namespace App\Domain\DataTable;

enum TableFieldType: string
{
    case TextType = "text";
    case NumberType = "number";
    case BooleanType = "boolean";
    case SelectType = "select";
    case DatetimeType = "datetime";
}

const TABLE_FIELD_SELECT_COLORS = ["red", "orange", "amber", "lime", "green", "cyan", "blue", "indigo", "fuchsia", "pink"];
