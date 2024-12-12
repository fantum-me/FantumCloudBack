<?php

namespace App\Domain\DataTable\Publisher;

use App\Domain\DataTable\Entity\TableRecord;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

#[AsEntityListener(event: Events::prePersist, method: "prePersist", entity: TableRecord::class)]
#[AsEntityListener(event: Events::preRemove, method: "preRemove", entity: TableRecord::class)]
class TableRecordPublisher
{
    public function __construct(
        private readonly HubInterface        $mercureHub,
        private readonly SerializerInterface $serializer
    )
    {
    }

    public function prePersist(TableRecord $record): void
    {
        $update = new Update(
            "database-update/" . $record->getDataTable()->getId()->toRfc4122(),
            json_encode([
                "type" => "table_record_insert",
                "object" => $this->serializer->normalize($record)
            ]),
            private: true
        );

        $this->mercureHub->publish($update);
    }

    public function preRemove(TableRecord $record): void
    {
        $update = new Update(
            "database-update/" . $record->getDataTable()->getId()->toRfc4122(),
            json_encode([
                "type" => "table_record_delete",
                "object" => ["id" => $record->getId()->toRfc4122()]
            ]),
            private: true
        );

        $this->mercureHub->publish($update);
    }
}
