<?php

namespace App\Service\ObjectMaker;

use App\Entity\Interface\StorageItemInterface;
use App\Entity\Workspace;
use App\Security\Permission;
use App\Service\PermissionService;
use Symfony\Component\Security\Core\User\UserInterface;

class UserAccessObjectService
{
    public function __construct(
        private readonly PermissionService $permissionService
    )
    {
    }

    public function getItemAccessObject(UserInterface $user, StorageItemInterface $resource): array
    {
        return [
            Permission::READ => $this->permissionService->hasItemPermission($user, Permission::READ, $resource),
            Permission::WRITE => $this->permissionService->hasItemPermission($user, Permission::WRITE, $resource),
            Permission::TRASH => $this->permissionService->hasItemPermission($user, Permission::TRASH, $resource),
            Permission::DELETE => $this->permissionService->hasItemPermission($user, Permission::DELETE, $resource),
            Permission::EDIT_PERMISSIONS => $this->permissionService->hasItemPermission($user, Permission::EDIT_PERMISSIONS, $resource),
        ];
    }

    public function getWorkspaceAccessObject(UserInterface $user, Workspace $workspace): array
    {
        return [
            Permission::READ => $this->permissionService->hasWorkspacePermission($user, Permission::READ, $workspace),
            Permission::WRITE => $this->permissionService->hasWorkspacePermission($user, Permission::WRITE, $workspace),
            Permission::TRASH => $this->permissionService->hasWorkspacePermission($user, Permission::TRASH, $workspace),
            Permission::DELETE => $this->permissionService->hasWorkspacePermission($user, Permission::DELETE, $workspace),
            Permission::EDIT_PERMISSIONS => $this->permissionService->hasWorkspacePermission($user, Permission::EDIT_PERMISSIONS, $workspace),
        ];
    }
}
