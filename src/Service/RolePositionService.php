<?php

namespace App\Service;

use App\Entity\Role;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;

class RolePositionService
{
    public function __construct(
        private readonly RoleRepository         $roleRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function setRolePosition(Role $targetRole, int $targetPosition): void
    {
        $roles = $this->roleRepository->findBy(["workspace" => $targetRole->getWorkspace()], ["position" => "ASC"]);

        if (sizeof($roles) === 0) {
            $targetRole->setPosition(0);
            return;
        }

        $roleIds = array_map(function ($role) {
            return $role->getId();
        }, $roles);

        if (in_array($targetRole->getId(), $roleIds)) {
            array_splice($roleIds, $targetRole->getPosition(), 1);
        } else {
            $roles[] = $targetRole;
        }

        array_splice($roleIds, $targetPosition, 0, $targetRole->getId());

        foreach ($roles as $role) {
            $position = array_search($role->getId(), $roleIds);
            $role->setPosition($position);
            $this->entityManager->persist($role);
        }
    }
}
