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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class PreviewFileController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/files/{id}/preview', name: 'api_files_preview', methods: "GET")]
    public function preview(
        File                                       $file,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User                        $user,
        Filesystem                                 $filesystem,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $itemPermissionService,
        StorageItemService                         $storageItemService
    ): BinaryFileResponse
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemService->assertInWorkspace($workspace, $file);
        $itemPermissionService->assertPermission($user, Permission::READ, $file);

        $path = $this->getParameter("workspace_path") . "/" . $file->getPreviewPath();
        if (!$filesystem->exists($path)) {
            throw new BadRequestHttpException("preview not found");
        }
        return $this->file($path);
    }
}
