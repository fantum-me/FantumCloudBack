<?php

namespace App\Domain\Workspace\Service;

use App\Domain\Member\Member;
use App\Domain\Workspace\Workspace;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class WorkspacePermissionService
{
    public function assertPermission(
        UserInterface $user,
        string        $permission,
        Workspace     $workspace
    ): void
    {
        self::assertAccess($user, $workspace);
        $member = $user->getWorkspaceMember($workspace);
        $hasPermission = self::hasWorkspacePermission($member, $permission, $workspace);

        if (!$hasPermission) {
            throw new AccessDeniedHttpException(
                sprintf(
                    "You don't have permission to %s (Workspace %s)",
                    strtolower($permission),
                    $workspace->getId()
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
}
