<?php

namespace App\Domain\DataTable\Validator;

use App\Domain\DataTable\DataViewFilterType;
use App\Domain\DataTable\Entity\DataView;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;

class ValidViewSettingsValidator extends ConstraintValidator
{
    public static function getArrayConstraint(): Assert\Collection
    {
        return new Assert\Collection([
            'target_field' => [
                new Assert\Type("string"),
                new Assert\Length(["max" => 32])
            ],
            'widths' => new Assert\Type("array"),
            'filters' => new Assert\Type("array")
        ], allowExtraFields: false, allowMissingFields: true);
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        $view = $this->context->getObject();
        assert($view instanceof DataView);
        $fieldIds = array_map(fn($f) => $f->getId()->toRfc4122(), $view->getDataTable()->getFields()->toArray());
        $this->context->getValidator()->validate($value, self::getArrayConstraint());

        if (array_key_exists("width", $value)) {
            foreach ($value["width"] as $id => $width) {
                if (!in_array($id, $fieldIds)) $this->context->buildViolation($constraint->message);
                if (!is_integer($width) || $width < 50) $this->context->buildViolation($constraint->message);
            }
        }

        if (array_key_exists("filters", $value) && !self::validateFilters($value["filters"], $fieldIds)) {
            $this->context->buildViolation($constraint->message);
        }
    }

    private function validateFilters(array $filters, array $fieldIds): bool
    {
        foreach ($filters as $id => $filter) {
            if (!is_numeric($id) || $id >= sizeof($filter) || $id < 0) return false;
            if (!array_key_exists("field_id", $filter) || !in_array($filter["field_id"], $fieldIds)) return false;
            if (!array_key_exists("operation", $filter) || !DataViewFilterType::tryFrom($filter["operation"])) return false;
            if (!array_key_exists("value", $filter) || !is_string($filter["value"]) || strlen($filter["value"]) > 512) return false;
        }

        return true;
    }
}
