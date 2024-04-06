<?php

namespace App\Service\ObjectMaker;

use App\Entity\Interface\PermissionManagerInterface;
use App\Security\Permission;

class PermissionManagerObjectService
{
    public function getPermissionManagerObject(PermissionManagerInterface $permissionManager): array
    {
        $object = [];
        foreach (Permission::PERMISSIONS as $permission) {
            $object[$permission] = $permissionManager->can($permission);
        }
        return $object;
    }
}
