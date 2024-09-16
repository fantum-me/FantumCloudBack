<?php

namespace App\Domain\Role\Controller;

use App\Domain\Role\Role;
use App\Domain\Role\Service\RolePositionService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ModifyRoleController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/roles/{id}', name: 'api_workspaces_roles_modify', methods: 'PATCH')]
    public function modify(
        Request                                    $request,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[MapEntity(id: 'id')] Role                $role,
        #[CurrentUser] User                        $user,
        RolePositionService                        $rolePositionService,
        ValidatorInterface                         $validator,
        EntityManagerInterface                     $entityManager,
        WorkspacePermissionService                 $workspacePermissionService
    ): JsonResponse
    {
        $workspacePermissionService->assertPermission($user, Permission::EDIT_PERMISSIONS, $workspace);
        $member = $user->getWorkspaceMember($workspace);
        if (!$member->isOwner() && $role->getPosition() >= $member->getRoles()[0]->getPosition()) {
            throw new AccessDeniedHttpException();
        }

        $name = RequestHandler::getRequestParameter($request, "name");
        $color = RequestHandler::getRequestParameter($request, "color");
        $position = RequestHandler::getRequestParameter($request, "position");
        $permissions = RequestHandler::getRequestParameter($request, "permissions") ?? [];

        if (!$role->isDefault() && $name) {
            $role->setName($name);
        }

        if (!$role->isDefault() && $color) {
            $role->setColor($color);
        }

        foreach ($permissions as $permission => $value) {
            $role->setPermission($permission, $value);
        }

        if (!$role->isDefault() && $position && $position !== $role->getPosition()) {
            if (!$member->isOwner() && $member->getRoles()[0]->getPosition() <= $position) {
                throw new AccessDeniedHttpException();
            }
            $rolePositionService->setRolePosition($role, $position);
        }

        if (count($errors = $validator->validate($role)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $entityManager->flush();

        return $this->json($role);
    }
}
