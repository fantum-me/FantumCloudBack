<?php

namespace App\Controller\Api\Workspace\Member;

use App\Entity\Member;
use App\Entity\User;
use App\Entity\Workspace;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ModifyMembersController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/members/{id}', name: 'api_workspaces_members_modify', methods: 'PATCH')]
    public function modify(
        Request $request,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User $user,
        Member $targetMember,
        EntityManagerInterface $entityManager,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertPermission($user, Permission::MANAGE_MEMBERS, $workspace);
        $member = $user->getWorkspaceMember($workspace);

        $roleIds = RequestHandler::getRequestParameter($request, "roles");
        if ($roleIds) {
            foreach ($workspace->getRoles() as $role) {
                if ($role->isDefault()) {
                    continue;
                }
                if (!in_array($role->getId(), $roleIds) && in_array($role, $targetMember->getRoles()->toArray())) {
                    if ($member->isOwner() || $member->getRoles()[0]->getPosition() > $role->getPosition()) {
                        $targetMember->removeRole($role);
                    } else {
                        throw new AccessDeniedHttpException("could not remove role " . $role->getId());
                    }
                } elseif (in_array($role->getId(), $roleIds) && !in_array(
                        $role,
                        $targetMember->getRoles()->toArray()
                    )) {
                    if ($member->isOwner() || $member->getRoles()[0]->getPosition() > $role->getPosition()) {
                        $targetMember->addRole($role);
                    } else {
                        throw new AccessDeniedHttpException("could not add role " . $role->getId());
                    }
                }
            }
        }

        $isOwner = RequestHandler::getRequestParameter($request, "is_owner");
        if ($isOwner && $member->isOwner()) {
            $targetMember->setIsOwner(true);
            $member->setIsOwner(false);
        }

        $entityManager->flush();


        return $this->json($targetMember);
    }
}
