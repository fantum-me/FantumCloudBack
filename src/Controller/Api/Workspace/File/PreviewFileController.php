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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class PreviewFileController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/files/{id}/preview', name: 'api_files_preview', methods: "GET")]
    public function preview(
        File $file,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User $user,
        Filesystem $filesystem,
        PermissionService $permissionService,
        StorageItemService $storageItemService
    ): BinaryFileResponse {
        $permissionService->assertAccess($user, $workspace);
        $storageItemService->assertInWorkspace($workspace, $file);
        $permissionService->assertPermission($user, Permission::READ, $file);

        $path = $this->getParameter("workspace_path") . "/" . $file->getPreviewPath();
        if (!$filesystem->exists($path)) {
            throw new BadRequestHttpException("preview not found");
        }
        return $this->file($path);
    }
}
