<?php

namespace App\Domain\StorageItem\Controller;

use App\Domain\StorageItem\StorageItemRepository;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GetWorkspaceTrashController extends AbstractController
{
    #[Route('/api/workspaces/{id}/trash', name: 'api_workspaces_trash', methods: "GET")]
    public function trash(
        Workspace                  $workspace,
        #[CurrentUser] User        $user,
        WorkspacePermissionService $workspacePermissionService,
        StorageItemRepository      $storageItemRepository,
    ): JsonResponse
    {
        $workspacePermissionService->assertAccess($user, $workspace);

        $items = $storageItemRepository->findBy(["workspace" => $workspace, "inTrash" => true]);

        return $this->json($items, 200, [], [
            "groups" => ["default", "item_details", "item_parents"]
        ]);
    }
}
