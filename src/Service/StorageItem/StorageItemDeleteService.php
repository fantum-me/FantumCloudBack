<?php

namespace App\Service\StorageItem;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\Interface\StorageItemInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class StorageItemDeleteService
{
    public function __construct(
        private readonly string                 $workspacePath,
        private readonly EntityManagerInterface $entityManager,
        private readonly Filesystem             $filesystem
    )
    {
    }

    public function deleteStorageItem(StorageItemInterface $item, $withFile = true): void
    {
        if ($withFile) $this->filesystem->remove($this->workspacePath . "/" . $item->getPath());

        if ($item instanceof File && $withFile) {
            $this->filesystem->remove($this->workspacePath . "/" . $item->getPreviewPath());
        }

        if ($item instanceof Folder) {
            $children = array_merge($item->getFiles()->toArray(), $item->getFolders()->toArray());
            foreach ($children as $c) $this->deleteStorageItem($c, false);
        }

        $this->entityManager->remove($item);
    }
}
