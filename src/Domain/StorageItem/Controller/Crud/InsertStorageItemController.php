<?php

namespace App\Domain\StorageItem\Controller\Crud;

use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\StorageItem\Service\StorageItemService;
use App\Domain\StorageItem\StorageItemTypeProvider;
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
        EntityManagerInterface                     $entityManager,
        StorageItemService                         $storageItemService,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $itemPermissionService,
        StorageItemTypeProvider                    $storageItemTypeProvider,
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

        if (!array_key_exists($type, StorageItemTypeProvider::TYPES))
            throw new BadRequestHttpException("invalid storage item type.");

        $type = StorageItemTypeProvider::TYPES[$type];
        $factory = $storageItemTypeProvider->getFactory($type);
        $item = $factory->handleInsertRequest($request, $name, $parent);

        $parent->updateVersion();
        $entityManager->flush();

        return $this->json($item, 200, [], [
            'groups' => ["default", "item_details", "item_parents"]
        ]);
    }
}
