<?php

namespace App\Domain\DataTable\Publisher;

use App\Domain\DataTable\Entity\TableField;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

#[AsEntityListener(event: Events::prePersist, method: "prePersist", entity: TableField::class)]
#[AsEntityListener(event: Events::preUpdate, method: "preUpdate", entity: TableField::class)]
#[AsEntityListener(event: Events::preRemove, method: "preRemove", entity: TableField::class)]
class TableFieldPublisher
{
    public function __construct(
        private readonly HubInterface        $mercureHub,
        private readonly SerializerInterface $serializer
    )
    {
    }

    public function prePersist(TableField $field): void
    {
        $update = new Update(
            "database-update/" . $field->getDataTable()->getId()->toRfc4122(),
            json_encode([
                "type" => "table_field_insert",
                "object" => $this->serializer->normalize($field)
            ]),
            private: true
        );

        $this->mercureHub->publish($update);
    }

    public function preUpdate(TableField $field, PreUpdateEventArgs $eventArgs): void
    {
        $changeSet = $eventArgs->getEntityChangeSet();
        if (sizeof($changeSet) == 1 && array_key_exists('position', $changeSet)) return;

        $update = new Update(
            "database-update/" . $field->getDataTable()->getId()->toRfc4122(),
            json_encode([
                "type" => "table_field_update",
                "object" => $this->serializer->normalize($field)
            ]),
            private: true
        );

        $this->mercureHub->publish($update);
    }

    public function preRemove(TableField $field): void
    {
        $update = new Update(
            "database-update/" . $field->getDataTable()->getId()->toRfc4122(),
            json_encode([
                "type" => "table_field_delete",
                "object" => ["id" => $field->getId()->toRfc4122()]
            ]),
            private: true
        );

        $this->mercureHub->publish($update);
    }

    public function publishNewPosition(TableField $field): void
    {
        $update = new Update(
            "database-update/" . $field->getDataTable()->getId()->toRfc4122(),
            json_encode([
                "type" => "table_field_reposition",
                "object" => [
                    "id" => $field->getId()->toRfc4122(),
                    "position" => $field->getPosition()
                ]
            ]),
            private: true
        );

        $this->mercureHub->publish($update);
    }
}
