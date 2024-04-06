<?php

namespace App\Controller\Api\File;

use App\Entity\Folder;
use App\Entity\User;
use App\Factory\FileFactory;
use App\Security\Permission;
use App\Service\ObjectMaker\StorageItemObjectService;
use App\Service\PermissionService;
use App\Service\StorageItem\StorageItemService;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class InsertFileController extends AbstractController
{
    #[Route('/api/files', "api_files_insert", methods: "POST")]
    public function insert(
        Request $request,
        #[CurrentUser] User $user,
        FileFactory $fileFactory,
        StorageItemService $storageItemService,
        StorageItemObjectService $storageItemObjectService,
        PermissionService $permissionService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $parentId = RequestHandler::getRequestParameter($request, "parent_id", true);
        $name = RequestHandler::getRequestParameter($request, "name", true);
        $ext = RequestHandler::getRequestParameter($request, "ext", true);
        $mime = RequestHandler::getRequestParameter($request, "mime", true);

        $parent = $storageItemService->getStorageItem(Folder::class, $parentId);
        $permissionService->assertPermission($user, Permission::WRITE, $parent);

        $file = $fileFactory->createFile($name, $ext, $mime, $parent);
        $entityManager->flush();

        return $this->json($storageItemObjectService->getFileObject($file));
    }
}
