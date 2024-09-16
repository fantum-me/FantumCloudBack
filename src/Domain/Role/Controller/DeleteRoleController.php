<?php

namespace App\Domain\Role\Controller;

use App\Domain\Role\Role;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DeleteRoleController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/roles/{id}', name: 'api_workspaces_roles_delete', methods: 'DELETE')]
    public function modify(
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[MapEntity(id: 'id')] Role                $role,
        #[CurrentUser] User                        $user,
        EntityManagerInterface                     $entityManager,
        WorkspacePermissionService                 $workspacePermissionService
    ): Response
    {
        $workspacePermissionService->assertPermission($user, Permission::EDIT_PERMISSIONS, $workspace);
        $member = $user->getWorkspaceMember($workspace);
        if (!$member->isOwner() && $role->getPosition() >= $member->getRoles()[0]->getPosition()) {
            throw new AccessDeniedHttpException();
        }

        $entityManager->remove($role);
        $entityManager->flush();

        return new Response("done");
    }
}
