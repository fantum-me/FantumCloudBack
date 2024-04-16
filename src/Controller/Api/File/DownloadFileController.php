<?php

namespace App\Controller\Api\File;

use App\Entity\File;
use App\Entity\User;
use App\Security\Permission;
use App\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DownloadFileController extends AbstractController
{
    #[Route('/api/files/{id}/download', name: 'api_files_download', methods: "GET")]
    public function download(
        File $file,
        #[CurrentUser] User $user,
        PermissionService $permissionService
    ): BinaryFileResponse {
        $permissionService->assertPermission($user, Permission::READ, $file);
        $path = $this->getParameter('workspace_path') . "/" . $file->getPath();
        return $this->file($path, $file->getName() . "." . $file->getExt());
    }
}
