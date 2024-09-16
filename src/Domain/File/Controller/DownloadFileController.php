<?php

namespace App\Domain\File\Controller;

use App\Domain\File\File;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\StorageItem\Service\StorageItemService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DownloadFileController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/files/{id}/download', name: 'api_files_download', methods: "GET")]
    public function download(
        File                                       $file,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User                        $user,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $itemPermissionService,
        StorageItemService                         $storageItemService
    ): BinaryFileResponse
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemService->assertInWorkspace($workspace, $file);
        $itemPermissionService->assertPermission($user, Permission::READ, $file);
        $path = $this->getParameter('workspace_path') . "/" . $file->getPath();
        return $this->file($path, $file->getName());
    }
}
