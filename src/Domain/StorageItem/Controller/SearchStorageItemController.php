<?php

namespace App\Domain\StorageItem\Controller;

use App\Domain\StorageItem\Service\StorageItemQueryService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SearchStorageItemController extends AbstractController
{
    #[Route('/api/workspaces/{id}/items/search', name: 'api_workspaces_items_search', methods: "GET", priority: 2)]
    public function search(
        Request                    $request,
        Workspace                  $workspace,
        #[CurrentUser] User        $user,
        WorkspacePermissionService $workspacePermissionService,
        StorageItemQueryService    $itemQueryService
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $items = $itemQueryService->handleRequest($request, $workspace, $user);
        return $this->json($items, context: ["groups" => ["default", "item_details"]]);
    }
}
