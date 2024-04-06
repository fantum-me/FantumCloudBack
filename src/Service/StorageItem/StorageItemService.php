<?php

namespace App\Service\StorageItem;

use App\Entity\Interface\StorageItemInterface;
use App\Utils\EntityTypeMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StorageItemService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }
    public function getStorageItem(string $type, string $id): ?StorageItemInterface
    {
        $item = $this->entityManager->find($type, $id);
        if (!$item) throw new BadRequestHttpException(EntityTypeMapper::getNameFromClass($type) . " $id not found");
        return $item;
    }
}
