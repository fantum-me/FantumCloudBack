<?php

namespace App\Service\StorageItem;

use App\Entity\Folder;
use App\Entity\Interface\StorageItemInterface;

class StorageItemTrashService
{
    public static function trashItem(StorageItemInterface $item): void
    {
        if (self::isItemOrParentInTrash($item)) return;

        $item->setInTrash(true);
        $item->updateVersion();

        if (get_class($item) === Folder::class) self::removeChildrenFromTrash($item);
    }

    private static function removeChildrenFromTrash(Folder $parent): void
    {
        foreach ($parent->getFiles() as $file) {
            if ($file->isInTrash()) {
                $file->setInTrash(false);
                $file->updateVersion();
            }
        }

        foreach ($parent->getFolders() as $folder) {
            if ($folder->isInTrash()) {
                $folder->setInTrash(false);
                $folder->updateVersion();
            }
            self::removeChildrenFromTrash($folder);
        }
    }

    private static function isItemOrParentInTrash(StorageItemInterface $item): bool
    {
        if ($item->isInTrash()) return true;
        $parent = $item->getFolder();
        if (!$parent) return false;
        else return self::isItemOrParentInTrash($parent);
    }
}
