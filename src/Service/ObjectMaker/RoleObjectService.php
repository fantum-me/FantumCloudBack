<?php

namespace App\Service\ObjectMaker;

use App\Entity\Role;

class RoleObjectService
{
    public function __construct(
        private readonly PermissionManagerObjectService $permissionObjectService
    )
    {
    }

    public function getRoleObject(Role $role): array
    {
        return [
            "id" => $role->getId(),
            "name" => $role->getName(),
            "color" => $role->getColor(),
            "is_default" => $role->isDefault(),
            "workspace_id" => $role->getWorkspace()->getId(),
            "permissions" => $this->permissionObjectService->getPermissionManagerObject($role),
            "position" => $role->getPosition()
        ];
    }
}
