<?php

namespace App\Domain\StorageItem\Controller\Crud;

use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\StorageItem\Service\StorageItemService;
use App\Domain\StorageItem\StorageItem;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GetStorageItemController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/items/{id}', name: 'api_workspaces_items_get', methods: "GET")]
    public function get(
        StorageItem                                $item,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User                        $user,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $itemPermissionService,
        StorageItemService                         $storageItemService
    ): JsonResponse
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemService->assertInWorkspace($workspace, $item);
        $itemPermissionService->assertPermission($user, Permission::READ, $item);
        return $this->json($item, 200, [], [
            'groups' => ["default", "item_details", "item_parents", "folder_children", "datatable_details"]
        ]);
    }
}
