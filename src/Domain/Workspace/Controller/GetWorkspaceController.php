<?php

namespace App\Domain\Workspace\Controller;

use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GetWorkspaceController extends AbstractController
{
    #[Route('/api/workspaces/{id}', name: 'api_workspaces_get', methods: "GET")]
    public function get(
        Workspace                  $workspace,
        #[CurrentUser] User        $user,
        WorkspacePermissionService $workspacePermissionService,
    ): JsonResponse
    {
        $workspacePermissionService->assertAccess($user, $workspace);

        return $this->json($workspace, 200, [], [
            "groups" => ["default", "workspace_details"]
        ]);
    }
}
