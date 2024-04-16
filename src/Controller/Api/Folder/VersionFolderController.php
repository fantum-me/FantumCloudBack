<?php

namespace App\Controller\Api\Folder;

use App\Entity\Folder;
use App\Entity\User;
use App\Security\Permission;
use App\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class VersionFolderController extends AbstractController
{
    #[Route('/api/folders/{id}/version', name: 'api_folders_version', methods: "GET")]
    public function version(
        Folder $folder,
        #[CurrentUser] User $user,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertPermission($user, Permission::READ, $folder);
        return $this->json(["version" => $folder->getVersion()]);
    }
}
