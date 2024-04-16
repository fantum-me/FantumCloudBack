<?php

namespace App\Controller\Api\File;

use App\Entity\File;
use App\Entity\User;
use App\Security\Permission;
use App\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class PreviewFileController extends AbstractController
{
    #[Route('/api/files/{id}/preview', name: 'api_files_preview', methods: "GET")]
    public function preview(
        File $file,
        #[CurrentUser] User $user,
        Filesystem $filesystem,
        PermissionService $permissionService
    ): BinaryFileResponse {
        $permissionService->assertPermission($user, Permission::READ, $file);

        $path = $this->getParameter("workspace_path") . "/" . $file->getPreviewPath();
        if (!$filesystem->exists($path)) {
            throw new BadRequestHttpException("preview not found");
        }
        return $this->file($path);
    }
}
