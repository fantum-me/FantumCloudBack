<?php

namespace App\Domain\Member\Controller;

use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListMembersController extends AbstractController
{
    #[Route('/api/workspaces/{id}/members', name: 'api_workspaces_members_list', methods: 'GET')]
    public function list(
        Workspace                  $workspace,
        #[CurrentUser] User        $user,
        WorkspacePermissionService $workspacePermissionService
    ): JsonResponse
    {
        $workspacePermissionService->assertPermission($user, Permission::MANAGE_MEMBERS, $workspace);
        return $this->json($workspace->getMembers());
    }
}
