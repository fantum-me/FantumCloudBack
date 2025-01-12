<?php

namespace App\Domain\DataTable\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ValidViewSettings extends Constraint
{
    public string $message = 'The view settings are not valid.';
}
