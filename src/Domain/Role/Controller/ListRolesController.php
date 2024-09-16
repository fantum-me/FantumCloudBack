<?php

namespace App\Domain\Role\Controller;

use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListRolesController extends AbstractController
{
    #[Route('/api/workspaces/{id}/roles', name: 'api_workspaces_roles_list', methods: 'GET')]
    public function list(
        Workspace                  $workspace,
        #[CurrentUser] User        $user,
        WorkspacePermissionService $workspacePermissionService
    ): JsonResponse
    {
        $workspacePermissionService->assertPermission($user, Permission::EDIT_PERMISSIONS, $workspace);
        return $this->json($workspace->getRoles());
    }
}
