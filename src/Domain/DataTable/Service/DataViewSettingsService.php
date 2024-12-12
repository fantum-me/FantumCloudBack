<?php

namespace App\Domain\DataTable\Service;

use App\Domain\DataTable\Entity\DataView;

class DataViewSettingsService
{
    public function validateFieldSettings(DataView $view, array $fieldSettings): bool
    {
        $fieldIds = array_map(fn($f) => $f->getId()->toRfc4122(), $view->getDataTable()->getFields()->toArray());

        foreach ($fieldSettings as $key => $value) {
            if (!in_array($key, $fieldIds) || !is_array($value)) return false;
            if (count($value) === 0) return true;
            if (count($value) > 1 || !key_exists("width", $value) || !is_numeric($value["width"])) return false;
        }

        return true;
    }
}
