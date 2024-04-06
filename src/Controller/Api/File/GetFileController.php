<?php

namespace App\Controller\Api\File;

use App\Entity\File;
use App\Entity\User;
use App\Security\Permission;
use App\Service\ObjectMaker\StorageItemObjectService;
use App\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GetFileController extends AbstractController
{
    #[Route('/api/files/{id}', name: 'api_files_get', methods: "GET")]
    public function get(
        File $file,
        #[CurrentUser] User $user,
        StorageItemObjectService $objectService,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertPermission($user, Permission::READ, $file);
        return $this->json($objectService->getFileObject($file));
    }
}
