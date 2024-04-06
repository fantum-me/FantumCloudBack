<?php

namespace App\Controller\Api\File;

use App\Entity\Folder;
use App\Entity\User;
use App\Factory\FileFactory;
use App\Security\Permission;
use App\Service\ObjectMaker\StorageItemObjectService;
use App\Service\PermissionService;
use App\Service\StorageItem\StorageItemService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UploadFileController extends AbstractController
{
    #[Route('/api/files/upload', name: 'api_files_upload', methods: "POST")]
    public function upload(
        Request                  $request,
        #[CurrentUser] User      $user,
        EntityManagerInterface   $entityManager,
        FileFactory              $fileFactory,
        StorageItemObjectService $objectService,
        StorageItemService       $storageItemService,
        PermissionService        $permissionService
    ): JsonResponse
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) throw new BadRequestHttpException("file not found");

        $folderId = $request->request->get('folder');
        if (!$folderId) throw new BadRequestHttpException("folder not found");
        $folder = $storageItemService->getStorageItem(Folder::class, $folderId);
        $permissionService->assertPermission($user, Permission::WRITE, $folder);

        $file = $fileFactory->createFileFromUpload($uploadedFile, $folder);
        $entityManager->flush();

        return $this->json($objectService->getFileObject($file));
    }
}
