<?php

namespace App\Domain\StorageItem\Service;

use App\Domain\File\File;
use App\Domain\Folder\Folder;
use App\Domain\StorageItem\StorageItemInterface;
use App\Domain\StorageItem\StorageItemTypeProvider;
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
        if (StorageItemTypeProvider::isInFilesystem($item)) {
            $basePath = $item->getPath();
            $previewBasePath = $item instanceof File ? $item->getPreviewPath() : null;

            $item->setFolder($target);

            $this->filesystem->rename(
                $this->workspacePath . "/" . $basePath,
                $this->workspacePath . "/" . $item->getPath()
            );

            if ($item instanceof File) {
                if ($this->filesystem->exists($this->workspacePath . "/" . $previewBasePath)) {
                    $this->filesystem->rename(
                        $this->workspacePath . "/" . $previewBasePath,
                        $this->workspacePath . "/" . $item->getPreviewPath()
                    );
                }
            }
        } else {
            $item->setFolder($target);
        }
    }
}
