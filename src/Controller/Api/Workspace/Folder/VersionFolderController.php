<?php

namespace App\Controller\Api\Workspace\Folder;

use App\Entity\Folder;
use App\Entity\User;
use App\Entity\Workspace;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Service\StorageItem\StorageItemService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class VersionFolderController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/folders/{id}/version', name: 'api_folders_version', methods: "GET")]
    public function version(
        Folder $folder,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User $user,
        PermissionService $permissionService,
        StorageItemService $storageItemService
    ): JsonResponse {
        $permissionService->assertAccess($user, $workspace);
        $storageItemService->assertInWorkspace($workspace, $folder);
        $permissionService->assertPermission($user, Permission::READ, $folder);
        return $this->json(["version" => $folder->getVersion()]);
    }
}
