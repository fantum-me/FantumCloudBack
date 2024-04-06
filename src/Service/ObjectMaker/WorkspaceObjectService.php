<?php

namespace App\Service\ObjectMaker;

use App\Entity\Workspace;
use App\Service\FileSizeService;
use Symfony\Bundle\SecurityBundle\Security;

class WorkspaceObjectService
{
    public function __construct(
        private readonly Security $security,
        private readonly RoleObjectService $roleObjectService,
        private readonly UserAccessObjectService $userAccessObjectService,
        private readonly string $workspacePath
    ) {
    }

    public function getWorkspaceObject(Workspace $workspace, ?array $scopes = null): array
    {
        $object = [
            "id" => $workspace->getId(),
            "name" => $workspace->getName(),
            "owner" => $workspace->getOwner()->getUser()->getId(),
            "root" => $workspace->getRootFolder()->getId(),
            "member_count" => sizeof($workspace->getMembers()),
            "used_space" => FileSizeService::getFolderSize($this->workspacePath . "/" . $workspace->getId()),
            "quota" => $workspace->getQuota()
        ];

        if (!$scopes || in_array("access", $scopes)) {
            $object["access"] = $this->userAccessObjectService->getWorkspaceAccessObject(
                $this->security->getUser(),
                $workspace
            );
        }

        if (!$scopes || in_array("roles", $scopes)) {
            $object["roles"] = [];
            foreach ($workspace->getRoles() as $role) {
                $object["roles"][] = $this->roleObjectService->getRoleObject($role);
            }
        }

        return $object;
    }
}
