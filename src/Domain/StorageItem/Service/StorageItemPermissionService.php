<?php

namespace App\Domain\StorageItem\Service;

use App\Domain\Member\Member;
use App\Domain\StorageItem\StorageItemInterface;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class StorageItemPermissionService
{
    public function __construct(
        private readonly WorkspacePermissionService $workspacePermissionService
    )
    {
    }

    public function assertPermission(
        UserInterface        $user,
        string               $permission,
        StorageItemInterface $resource
    ): void
    {
        $workspace = $resource->getWorkspace();
        $this->workspacePermissionService->assertAccess($user, $workspace);
        $member = $user->getWorkspaceMember($workspace);

        $hasPermission = self::hasItemPermission($member, $permission, $resource);

        if (!$hasPermission) {
            throw new AccessDeniedHttpException(
                sprintf(
                    "You don't have permission to %s (Item %s)",
                    strtolower($permission),
                    $resource->getId()
                )
            );
        }
    }

    public function hasItemPermission(Member $member, string $permission, StorageItemInterface $resource): bool
    {
        $workspace = $member->getWorkspace();

        if ($workspace->getOwner() === $member) {
            return true;
        }

        $accessControls = $resource->getAccessControls();

        $permissionDenied = false;
        foreach ($accessControls as $accessControl) {
            if (!$member->getRoles()->contains($accessControl->getRole())) {
                continue;
            }

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
            return $this->workspacePermissionService->hasWorkspacePermission($member, $permission, $workspace);
        }
    }
}
