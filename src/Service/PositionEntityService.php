<?php

namespace App\Service;

use App\Entity\Interface\PositionEntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class PositionEntityService
{
    public function __construct(
        private readonly EntityManagerInterface    $entityManager,
        private readonly PropertyAccessorInterface $propertyAccessor
    )
    {
    }

    public function setEntityPosition(PositionEntityInterface $targetEntity, string $commonAttribute, int $targetPosition): void
    {
        $entities = $this->entityManager->getRepository($targetEntity::class)->findBy(
            [$commonAttribute => $this->propertyAccessor->getValue($targetEntity, $commonAttribute)],
            ["position" => "ASC"]
        );

        if (sizeof($entities) === 0) {
            $targetEntity->setPosition(0);
            return;
        }

        $ids = array_map(function ($e) {
            return $e->getId();
        }, $entities);

        if (in_array($targetEntity->getId(), $ids)) {
            array_splice($ids, $targetEntity->getPosition(), 1);
        } else {
            $entities[] = $targetEntity;
        }

        array_splice($ids, $targetPosition, 0, $targetEntity->getId());

        foreach ($entities as $e) {
            $position = array_search($e->getId(), $ids);
            $e->setPosition($position);
            $this->entityManager->persist($e);
        }
    }
}
