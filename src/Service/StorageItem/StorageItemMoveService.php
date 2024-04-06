<?php

namespace App\Service\StorageItem;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\Interface\StorageItemInterface;
use Symfony\Component\Filesystem\Filesystem;

class StorageItemMoveService
{
    public function __construct(
        private readonly string     $workspacePath,
        private readonly Filesystem $filesystem
    )
    {
    }

    public function moveStorageItem(StorageItemInterface $item, Folder $target): void
    {
        $basePath = $item->getPath();
        if ($item instanceof File) $previewBasePath = $item->getPreviewPath();

        $item->setFolder($target);

        $this->filesystem->rename(
            $this->workspacePath . "/" . $basePath,
            $this->workspacePath . "/" . $item->getPath()
        );

        if ($item instanceof File && $this->filesystem->exists($this->workspacePath . "/" . $previewBasePath)) {
            $this->filesystem->rename(
                $this->workspacePath . "/" . $previewBasePath,
                $this->workspacePath . "/" . $item->getPreviewPath()
            );
        }
    }
}
