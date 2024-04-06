<?php

namespace App\Service;

use App\Entity\Interface\StorageItemInterface;
use App\Entity\Workspace;
use App\Utils\EntityTypeMapper;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class PermissionService
{
    public function hasItemPermission(UserInterface $user, string $permission, StorageItemInterface $resource): bool
    {
        $workspace = $resource->getWorkspace();
        $member = $user->getWorkspaceMember($workspace);

        if (!$member) {
            return false;
        }
        if ($workspace->getOwner() === $member) {
            return true;
        }

        $accessControls = $resource->getAccessControls();

        $permissionDenied = false;
        foreach ($accessControls as $accessControl) {
            $allowed = $accessControl->can($permission);
            if ($allowed === true) {
                return true;
            } elseif ($allowed === false) {
                $permissionDenied = true;
            }
        }

        if ($permissionDenied) {
            return false;
        }

        // Check parent resource permissions recursively
        $folder = $resource->getFolder();
        if ($folder !== null) {
            return $this->hasItemPermission($user, $permission, $folder);
        } else {
            return $this->hasWorkspacePermission($user, $permission, $workspace);
        }
    }

    public function hasWorkspacePermission(UserInterface $user, string $permission, Workspace $workspace): bool
    {
        $member = $user->getWorkspaceMember($workspace);
        if ($workspace->getOwner() === $member) {
            return true;
        }

        foreach ($member->getRoles() as $role) {
            if ($role->can($permission)) {
                return true;
            }
        }
        return false;
    }

    public function assertPermission(UserInterface $user, string $permission, StorageItemInterface $item): void
    {
        if (!PermissionService::hasItemPermission($user, $permission, $item)) {
            $className = EntityTypeMapper::getNameFromClass($item::class);
            throw new AccessDeniedHttpException(
                "you don't have permission to read " . $className . " " . $item->getId()
            );
        }
    }

    public function assertAccess(UserInterface $user, Workspace $workspace): void
    {
        if (!$user->isInWorkspace($workspace)) {
            throw new AccessDeniedHttpException(
                "you don't have access to workspace " . $workspace->getId()
            );
        }
    }
}
