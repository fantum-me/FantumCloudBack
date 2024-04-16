<?php

namespace App\Controller\Api\Workspace\Member;

use App\Entity\User;
use App\Entity\Workspace;
use App\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListMemberController extends AbstractController
{
    #[Route('/api/workspaces/{id}/members', name: 'api_workspaces_members_list', methods: 'GET')]
    public function list(
        Workspace $workspace,
        #[CurrentUser] User $user,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertAccess($user, $workspace);
        return $this->json($workspace->getMembers());
    }
}
