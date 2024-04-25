<?php

namespace App\Controller\Api\Workspace\Role;

use App\Entity\User;
use App\Entity\Workspace;
use App\Factory\RoleFactory;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class InsertRoleController extends AbstractController
{
    #[Route('/api/workspaces/{id}/roles', name: 'api_workspaces_roles_insert', methods: 'POST')]
    public function insert(
        Request $request,
        Workspace $workspace,
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
        RoleFactory $roleFactory,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertPermission($user, Permission::EDIT_PERMISSIONS, $workspace);
        $name = RequestHandler::getRequestParameter($request, "name", true);

        $role = $roleFactory->createRole($name, 1, $workspace, [
            Permission::READ => false,
            Permission::WRITE => false,
            Permission::TRASH => false,
            Permission::DELETE => false,
            Permission::EDIT_PERMISSIONS => false
        ]);

        $entityManager->flush();

        return $this->json($role);
    }
}
