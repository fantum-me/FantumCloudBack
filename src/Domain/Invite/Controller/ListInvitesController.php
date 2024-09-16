<?php

namespace App\Domain\Invite\Controller;

use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListInvitesController extends AbstractController
{
    #[Route('/api/workspaces/{id}/invites', name: 'api_workspaces_invites_list', methods: 'GET')]
    public function list(
        Workspace                    $workspace,
        #[CurrentUser] User          $user,
        WorkspacePermissionService   $workspacePermissionService,
        StorageItemPermissionService $itemPermissionService
    ): JsonResponse
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        return $this->json($workspace->getInvites());
    }
}
