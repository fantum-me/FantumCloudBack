<?php

namespace App\Controller\Api\Folder;

use App\Entity\Folder;
use App\Entity\User;
use App\Factory\FolderFactory;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Service\StorageItem\StorageItemService;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class InsertFolderController extends AbstractController
{
    #[Route('/api/folders', name: 'api_folders_insert', methods: "POST")]
    public function insert(
        Request $request,
        #[CurrentUser] User $user,
        FolderFactory $folderFactory,
        EntityManagerInterface $entityManager,
        StorageItemService $storageItemService,
        PermissionService $permissionService
    ): JsonResponse {
        $name = RequestHandler::getRequestParameter($request, "name", true);
        $parentId = RequestHandler::getRequestParameter($request, "parent_id", true);
        $parent = $storageItemService->getStorageItem(Folder::class, $parentId);
        $permissionService->assertPermission($user, Permission::WRITE, $parent);

        $folder = $folderFactory->createFolder($name, $parent);

        $entityManager->flush();

        return $this->json($folder, 200, [], [
            'groups' => ["default", "folder_details", "folder_parents"]
        ]);
    }
}
