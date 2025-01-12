<?php

namespace App\Domain\DataTable\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;

class ValidViewSettingsValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        $view = $this->context->getObject();
        $fieldIds = array_map(fn($f) => $f->getId()->toRfc4122(), $view->getDataTable()->getFields()->toArray());
        $this->context->getValidator()->validate($value, self::getArrayConstraint());

        if (array_key_exists("width", $value)) {
            foreach ($value["width"] as $id => $width) {
                if (!in_array($id, $fieldIds)) $this->context->buildViolation($constraint->message);
                if (!is_integer($width) || $width < 50) $this->context->buildViolation($constraint->message);
            }
        }
    }

    public static function getArrayConstraint(): Assert\Collection
    {
        return new Assert\Collection([
            'widths' => new Assert\Type("array")
        ], allowExtraFields: false, allowMissingFields: true);
    }
}
