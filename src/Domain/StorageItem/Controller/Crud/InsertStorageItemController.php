<?php

namespace App\Domain\StorageItem\Controller\Crud;

use App\Domain\File\FileFactory;
use App\Domain\Folder\FolderFactory;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\StorageItem\Service\StorageItemService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
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
        Request                                    $request,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User                        $user,
        FolderFactory                              $folderFactory,
        FileFactory                                $fileFactory,
        EntityManagerInterface                     $entityManager,
        StorageItemService                         $storageItemService,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $itemPermissionService
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);

        $type = RequestHandler::getRequestParameter($request, "type", true);

        $name = RequestHandler::getRequestParameter($request, "name", true);
        $parentId = RequestHandler::getRequestParameter($request, "parent_id", true);

        $parent = $storageItemService->getStorageItem($parentId);
        $parent = $storageItemService->assertFolder($parent);
        $storageItemService->assertInWorkspace($workspace, $parent);
        $itemPermissionService->assertPermission($user, Permission::WRITE, $parent);

        if ($type === "file") {
            $mime = RequestHandler::getRequestParameter($request, "mime", true);
            $item = $fileFactory->createFile($name, $mime, $parent);
        } elseif ($type === "folder") {
            $item = $folderFactory->createFolder($name, $parent);
        } else {
            throw new BadRequestHttpException("type need be either 'file' or 'folder'");
        }

        $parent->updateVersion();
        $entityManager->flush();

        return $this->json($item, 200, [], [
            'groups' => ["default", "item_details", "item_parents"]
        ]);
    }
}
