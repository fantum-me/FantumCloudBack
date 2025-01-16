<?php

namespace App\Domain\DataTable;

enum DataViewFilterType: string
{
    case Is = 'is';
    case IsNot = 'is_not';
    case Contains = 'contains';
    case DoesNotContain = 'does_not_contain';
    case StartsWith = 'starts_with';
    case EndsWith = 'ends_with';
    case IsEmpty = 'is_empty';
    case IsNotEmpty = 'is_not_empty';

    case IsGreater = 'is_greater';
    case IsGreaterOrEqual = 'is_greater_or_equal';
    case IsLower = 'is_lower';
    case IsLowerOrEqual = 'is_lower_or_equal';

    case IsBefore = 'is_before';
    case IsAfter = 'is_after';
}
