<?php

namespace App\Controller\Api\Workspace\Item;

use App\Entity\User;
use App\Entity\Workspace;
use App\Service\PermissionService;
use App\Service\StorageItem\StorageItemQueryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SearchStorageItemController extends AbstractController
{
    #[Route('/api/workspaces/{id}/items/search', name: 'api_workspaces_items_search', methods: "GET", priority: 2)]
    public function search(
        Request $request,
        Workspace $workspace,
        #[CurrentUser] User $user,
        PermissionService $permissionService,
        StorageItemQueryService $itemQueryService
    ): Response
    {
        $permissionService->assertAccess($user, $workspace);
        $items = $itemQueryService->handleRequest($request, $workspace, $user);
        return $this->json($items, context: ["groups" => ["default", "item_details"]]);
    }
}
