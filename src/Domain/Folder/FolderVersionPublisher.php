<?php

namespace App\Domain\Folder;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsEntityListener(event: Events::preUpdate, method: "preUpdate", entity: Folder::class)]
class FolderVersionPublisher
{
    public function __construct(
        private readonly HubInterface $mercureHub
    )
    {
    }

    public function preUpdate(Folder $folder, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('version')) {
            $update = new Update(
                "folder-version/" . $folder->getId()->toRfc4122(),
                $folder->getVersion(),
                private: true
            );

            $this->mercureHub->publish($update);
        }
    }
}
