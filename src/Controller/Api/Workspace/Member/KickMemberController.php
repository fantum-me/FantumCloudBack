<?php

namespace App\Controller\Api\Workspace\Member;

use App\Entity\Member;
use App\Entity\User;
use App\Entity\Workspace;
use App\Security\Permission;
use App\Service\PermissionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class KickMemberController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/members/{id}/kick', name: 'api_workspaces_members_kick', methods: 'POST')]
    public function modify(
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User $user,
        Member $targetMember,
        EntityManagerInterface $entityManager,
        PermissionService $permissionService
    ): Response {
        $permissionService->assertPermission($user, Permission::MANAGE_MEMBERS, $workspace);
        $member = $user->getWorkspaceMember($workspace);

        if ($targetMember->isOwner() || (!$member->isOwner() && $member->getPosition() <= $targetMember->getPosition(
                ))) {
            throw new AccessDeniedHttpException("You don't have permission to kick this member");
        }

        $entityManager->remove($targetMember);
        $entityManager->flush();

        return new Response("done");
    }
}
