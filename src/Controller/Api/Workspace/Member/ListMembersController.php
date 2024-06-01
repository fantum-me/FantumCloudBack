<?php

namespace App\Controller\Api\Workspace\Member;

use App\Entity\User;
use App\Entity\Workspace;
use App\Security\Permission;
use App\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListMembersController extends AbstractController
{
    #[Route('/api/workspaces/{id}/members', name: 'api_workspaces_members_list', methods: 'GET')]
    public function list(
        Workspace $workspace,
        #[CurrentUser] User $user,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertPermission($user, Permission::MANAGE_MEMBERS, $workspace);
        return $this->json($workspace->getMembers());
    }
}
