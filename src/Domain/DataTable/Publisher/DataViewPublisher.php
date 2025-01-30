<?php

namespace App\Domain\DataTable\Publisher;

use App\Domain\DataTable\Entity\DataView;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

#[AsEntityListener(event: Events::prePersist, method: "prePersist", entity: DataView::class)]
#[AsEntityListener(event: Events::preUpdate, method: "preUpdate", entity: DataView::class)]
#[AsEntityListener(event: Events::preRemove, method: "preRemove", entity: DataView::class)]
class DataViewPublisher
{
    public function __construct(
        private readonly HubInterface        $mercureHub,
        private readonly SerializerInterface $serializer
    )
    {
    }

    public function prePersist(DataView $view): void
    {
        $update = new Update(
            "database-update/" . $view->getDataTable()->getId()->toRfc4122(),
            json_encode([
                "type" => "database_view_insert",
                "object" => $this->serializer->normalize($view)
            ]),
            private: true
        );

        $this->mercureHub->publish($update);
    }

    public function preUpdate(DataView $view): void
    {
        $update = new Update(
            "database-update/" . $view->getDataTable()->getId()->toRfc4122(),
            json_encode([
                "type" => "database_view_update",
                "object" => $this->serializer->normalize($view)
            ]),
            private: true
        );

        $this->mercureHub->publish($update);
    }

    public function preRemove(DataView $view): void
    {
        $update = new Update(
            "database-update/" . $view->getDataTable()->getId()->toRfc4122(),
            json_encode([
                "type" => "database_view_delete",
                "object" => ["id" => $view->getId()->toRfc4122()]
            ]),
            private: true
        );

        $this->mercureHub->publish($update);
    }
}
