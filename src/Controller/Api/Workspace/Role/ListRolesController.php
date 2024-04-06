<?php

namespace App\Controller\Api\Workspace\Role;

use App\Entity\User;
use App\Entity\Workspace;
use App\Security\Permission;
use App\Service\ObjectMaker\RoleObjectService;
use App\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListRolesController extends AbstractController
{
    #[Route('/api/workspaces/{id}/roles', name: 'api_workspaces_roles_list', methods: 'GET')]
    public function list(
        Workspace           $workspace,
        #[CurrentUser] User $user,
        RoleObjectService   $roleObjectService,
        PermissionService   $permissionService
    ): Response
    {
        $permissionService->hasWorkspacePermission($user, Permission::EDIT_PERMISSIONS, $workspace);
        $roles = [];
        foreach ($workspace->getRoles() as $role) $roles[] = $roleObjectService->getRoleObject($role);
        return $this->json($roles);
    }
}
