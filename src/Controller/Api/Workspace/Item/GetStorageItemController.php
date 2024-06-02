<?php

namespace App\Controller\Api\Workspace\Item;

use App\Entity\StorageItem;
use App\Entity\User;
use App\Entity\Workspace;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Service\StorageItem\StorageItemService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GetStorageItemController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/items/{id}', name: 'api_workspaces_items_get', methods: "GET")]
    public function get(
        StorageItem $item,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User $user,
        PermissionService $permissionService,
        StorageItemService $storageItemService
    ): JsonResponse {
        $permissionService->assertAccess($user, $workspace);
        $storageItemService->assertInWorkspace($workspace, $item);
        $permissionService->assertPermission($user, Permission::READ, $item);
        return $this->json($item, 200, [], [
            'groups' => ["default", "item_details", "item_parents", "folder_children"]
        ]);
    }
}
