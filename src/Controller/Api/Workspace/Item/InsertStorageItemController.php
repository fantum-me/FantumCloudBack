<?php

namespace App\Controller\Api\Workspace\Item;

use App\Entity\User;
use App\Entity\Workspace;
use App\Factory\FileFactory;
use App\Factory\FolderFactory;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Service\StorageItem\StorageItemService;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class InsertStorageItemController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/items', name: 'api_workspaces_items_insert', methods: "POST")]
    public function insert(
        Request $request,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User $user,
        FolderFactory $folderFactory,
        FileFactory $fileFactory,
        EntityManagerInterface $entityManager,
        StorageItemService $storageItemService,
        PermissionService $permissionService
    ): Response
    {
        $permissionService->assertAccess($user, $workspace);

        $type = RequestHandler::getRequestParameter($request, "type", true);

        $name = RequestHandler::getRequestParameter($request, "name", true);
        $parentId = RequestHandler::getRequestParameter($request, "parent_id", true);

        $parent = $storageItemService->getStorageItem($parentId);
        $parent = $storageItemService->assertFolder($parent);
        $storageItemService->assertInWorkspace($workspace, $parent);
        $permissionService->assertPermission($user, Permission::WRITE, $parent);

        if ($type === "file") {
            $ext = RequestHandler::getRequestParameter($request, "ext", true);
            $mime = RequestHandler::getRequestParameter($request, "mime", true);
            $item = $fileFactory->createFile($name, $ext, $mime, $parent);
        } elseif ($type === "folder") {
            $item = $folderFactory->createFolder($name, $parent);
        } else {
            throw new BadRequestHttpException("type need be either 'file' or 'folder'");
        }

        $entityManager->flush();

        return $this->json($item, 200, [], [
            'groups' => ["default", "item_details", "item_parents"]
        ]);
    }
}
