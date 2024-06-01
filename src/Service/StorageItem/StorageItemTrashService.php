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

        if ($item instanceof Folder) self::removeChildrenFromTrash($item);
    }

    private static function removeChildrenFromTrash(Folder $parent): void
    {
        foreach ($parent->getItems() as $item) {
            if ($item->isInTrash()) {
                $item->setInTrash(false);
                $item->updateVersion();
            }
            if ($item instanceof Folder) {
                self::removeChildrenFromTrash($item);
            }
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
