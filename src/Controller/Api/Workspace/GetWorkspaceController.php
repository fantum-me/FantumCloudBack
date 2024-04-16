<?php

namespace App\Controller\Api\Workspace;

use App\Entity\User;
use App\Entity\Workspace;
use App\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GetWorkspaceController extends AbstractController
{
    #[Route('/api/workspaces/{id}', name: 'api_workspaces_get', methods: "GET")]
    public function get(
        Workspace $workspace,
        #[CurrentUser] User $user,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertAccess($user, $workspace);

        return $this->json($workspace, 200, [], [
            "groups" => ["default", "workspace_details"]
        ]);
    }
}
