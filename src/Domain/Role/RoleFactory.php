<?php

namespace App\Domain\Role;

use App\Domain\Workspace\Workspace;
use App\Service\PositionEntityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoleFactory
{
    public function __construct(
        private readonly PositionEntityService $positionEntityService,
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
            ->setIsDefault($isDefault);

        $workspace->addRole($role);

        foreach ($permissions as $permission => $value) {
            $role->setPermission($permission, $value);
        }

        $this->positionEntityService->setEntityPosition($role, "workspace", $position);

        if (count($errors = $this->validator->validate($role)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $this->entityManager->persist($role);

        return $role;
    }
}
