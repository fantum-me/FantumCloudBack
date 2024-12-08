<?php

namespace App\Domain\DataTable\Publisher;

use App\Domain\DataTable\Entity\TableField;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

#[AsEntityListener(event: Events::prePersist, method: "prePersist", entity: TableField::class)]
#[AsEntityListener(event: Events::preUpdate, method: "preUpdate", entity: TableField::class)]
#[AsEntityListener(event: Events::preRemove, method: "preRemove", entity: TableField::class)]
class TableFieldPublisher
{
    public function __construct(
        private readonly HubInterface     $mercureHub,
        private readonly ObjectNormalizer $normalizer
    )
    {
    }

    public function prePersist(TableField $field): void
    {
        $update = new Update(
            "database-update/" . $field->getDataTable()->getId()->toRfc4122(),
            json_encode([
                "type" => "table_field_insert",
                "object" => $this->normalizer->normalize($field)
            ]),
            private: true
        );

        $this->mercureHub->publish($update);
    }

    public function preUpdate(TableField $field): void
    {
        $update = new Update(
            "database-update/" . $field->getDataTable()->getId()->toRfc4122(),
            json_encode([
                "type" => "table_field_update",
                "object" => $this->normalizer->normalize($field)
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
                "id" => $field->getId()->toRfc4122()
            ]),
            private: true
        );

        $this->mercureHub->publish($update);
    }
}
