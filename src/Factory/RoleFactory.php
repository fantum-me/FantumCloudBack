<?php

namespace App\Factory;

use App\Entity\Role;
use App\Entity\Workspace;
use App\Service\RolePositionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoleFactory
{
    public function __construct(
        private readonly RolePositionService    $rolePositionService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface     $validator
    )
    {
    }

    public function createRole(
        string    $name,
        int       $position,
        Workspace $workspace,
        array     $permissions,
        bool      $isDefault = false
    ): Role
    {
        $role = new Role();

        $role->setName($name)
            ->setWorkspace($workspace)
            ->setIsDefault($isDefault);

        foreach ($permissions as $permission => $value) {
            $role->setPermission($permission, $value);
        }

        $this->rolePositionService->setRolePosition($role, $position);

        if (count($errors = $this->validator->validate($role)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $this->entityManager->persist($role);

        return $role;
    }
}
