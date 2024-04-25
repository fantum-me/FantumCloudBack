<?php

namespace App\Service;

use App\Entity\Interface\StorageItemInterface;
use App\Entity\Member;
use App\Entity\Workspace;
use App\Utils\EntityTypeMapper;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class PermissionService
{
    public function hasItemPermission(Member $member, string $permission, StorageItemInterface $resource): bool
    {
        $workspace = $member->getWorkspace();

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
        if ($folder = $resource->getFolder()) {
            return $this->hasItemPermission($member, $permission, $folder);
        } else {
            return $this->hasWorkspacePermission($member, $permission, $workspace);
        }
    }

    public function hasWorkspacePermission(?Member $member, string $permission, Workspace $workspace): bool
    {
        if (!$member) {
            return false;
        }
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

    public function assertPermission(
        UserInterface $user,
        string $permission,
        StorageItemInterface|Workspace $resource
    ): void {
        $workspace = $resource instanceof Workspace ? $resource : $resource->getWorkspace();

        self::assertAccess($user, $workspace);
        $member = $user->getWorkspaceMember($workspace);

        if ($resource instanceof Workspace) {
            $hasPermission = self::hasWorkspacePermission($member, $permission, $resource);
        } else {
            $hasPermission = self::hasItemPermission($member, $permission, $resource);
        }

        if (!$hasPermission) {
            throw new AccessDeniedHttpException(
                sprintf(
                    "You don't have permission to %s (%s %s)",
                    strtolower($permission),
                    EntityTypeMapper::getNameFromClass($resource::class),
                    $resource->getId()
                )
            );
        }
    }

    public function assertAccess(UserInterface $user, Workspace $workspace): void
    {
        if (!$user->isInWorkspace($workspace)) {
            throw new AccessDeniedHttpException(
                sprintf("You don't have access to workspace %s", $workspace->getId())
            );
        }
    }
}
