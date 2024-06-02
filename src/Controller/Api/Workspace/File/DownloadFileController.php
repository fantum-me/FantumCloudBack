<?php

namespace App\Controller\Api\Workspace\File;

use App\Entity\File;
use App\Entity\User;
use App\Entity\Workspace;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Service\StorageItem\StorageItemService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DownloadFileController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/files/{id}/download', name: 'api_files_download', methods: "GET")]
    public function download(
        File $file,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User $user,
        PermissionService $permissionService,
        StorageItemService $storageItemService
    ): BinaryFileResponse {
        $permissionService->assertAccess($user, $workspace);
        $storageItemService->assertInWorkspace($workspace, $file);
        $permissionService->assertPermission($user, Permission::READ, $file);
        $path = $this->getParameter('workspace_path') . "/" . $file->getPath();
        return $this->file($path, $file->getName() . "." . $file->getExt());
    }
}
