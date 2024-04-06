<?php

namespace App\Controller\Api\Workspace\Invite;

use App\Entity\User;
use App\Entity\Workspace;
use App\Service\ObjectMaker\InviteCodeObjectService;
use App\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListInvitesController extends AbstractController
{
    #[Route('/api/workspaces/{id}/invites', name: 'api_workspaces_invites_list', methods: 'GET')]
    public function list(
        Workspace               $workspace,
        #[CurrentUser] User     $user,
        InviteCodeObjectService $inviteCodeObjectService,
        PermissionService       $permissionService
    ): JsonResponse
    {
        $permissionService->assertAccess($user, $workspace);
        $invites = [];
        foreach ($workspace->getInvites() as $invite) {
            $invites[] = $inviteCodeObjectService->getInviteCodeObject($invite);
        }
        return $this->json($invites);
    }
}
