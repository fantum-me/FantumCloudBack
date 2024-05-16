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

class GetFolderController extends AbstractController
{
    #[Route('/api/folders/{id}', name: 'api_folders_get', methods: "GET")]
    public function get(
        Folder $folder,
        #[CurrentUser] User $user,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertPermission($user, Permission::READ, $folder);
        return $this->json($folder, 200, [], [
            'groups' => ["default", "folder_details", "folder_parents", "folder_children"]
        ]);
    }
}
