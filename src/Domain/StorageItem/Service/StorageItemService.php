<?php

namespace App\Domain\StorageItem\Service;

use App\Domain\Folder\Folder;
use App\Domain\StorageItem\StorageItem;
use App\Domain\StorageItem\StorageItemRepository;
use App\Domain\Workspace\Workspace;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StorageItemService
{
    public function __construct(
        private readonly StorageItemRepository $storageItemRepository
    )
    {
    }

    public function getStorageItem(string $id, bool $required = true): ?StorageItem
    {
        $item = $this->storageItemRepository->find($id);
        if ($required && !$item) {
            throw new BadRequestHttpException("item $id not found");
        }
        return $item;
    }

    public function assertFolder(StorageItem $item): Folder
    {
        if (!($item instanceof Folder)) {
            throw new BadRequestHttpException(sprintf("item %s is not a folder", $item->getId()));
        }
        return $item;
    }

    public function assertInWorkspace(Workspace $workspace, StorageItem $item): void
    {
        if ($item->getWorkspace() !== $workspace) {
            throw new BadRequestHttpException(sprintf("invalid workspace for item %s", $item->getId()));
        }
    }
}
