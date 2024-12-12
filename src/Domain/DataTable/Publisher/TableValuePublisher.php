<?php

namespace App\Domain\DataTable\Publisher;

use App\Domain\DataTable\Entity\TableField;
use App\Domain\DataTable\Entity\TableRecord;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/*
 * This class is not an EntityListener.
 * It have to be called by controller.
 */

class TableValuePublisher
{
    public function __construct(
        private readonly HubInterface $mercureHub,
    )
    {
    }

    public function sendTableValueUpdate(TableField $field, TableRecord $record, string $value): void
    {
        $update = new Update(
            "database-update/" . $field->getDataTable()->getId()->toRfc4122(),
            json_encode([
                "type" => "table_value_update",
                "object" => [
                    "id" => "_",
                    "field_id" => $field->getId()->toRfc4122(),
                    "record_id" => $record->getId()->toRfc4122(),
                    "value" => $value
                ]
            ]),
            private: true
        );

        $this->mercureHub->publish($update);
    }

}
