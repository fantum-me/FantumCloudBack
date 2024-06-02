<?php

namespace App\Controller\Api\Workspace\File;

use App\Entity\Folder;
use App\Entity\User;
use App\Entity\Workspace;
use App\Factory\FileFactory;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Service\StorageItem\StorageItemService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UploadFileController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/files/upload', name: 'api_files_upload', methods: "POST")]
    public function upload(
        Request $request,
        #[CurrentUser] User $user,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        EntityManagerInterface $entityManager,
        FileFactory $fileFactory,
        StorageItemService $storageItemService,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertAccess($user, $workspace);
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException("file not found");
        }

        $parentId = $request->request->get('folder');
        if (!$parentId) {
            throw new BadRequestHttpException("folder not found");
        }
        $parent = $storageItemService->getStorageItem($parentId);
        $parent = $storageItemService->assertFolder($parent);
        $storageItemService->assertInWorkspace($workspace, $parent);
        $permissionService->assertPermission($user, Permission::WRITE, $parent);

        $file = $fileFactory->createFileFromUpload($uploadedFile, $parent);
        $entityManager->flush();

        return $this->json($file, 200, [], [
            "groups" => ["default", "item_details"]
        ]);
    }
}
