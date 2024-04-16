<?php

namespace App\Controller\Api\Workspace\Invite;

use App\Entity\Invite;
use App\Entity\User;
use App\Entity\Workspace;
use App\Service\PermissionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class InsertInviteController extends AbstractController
{
    #[Route('/api/workspaces/{id}/invites', name: 'api_workspaces_invites_insert', methods: 'POST')]
    public function insert(
        Workspace $workspace,
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertAccess($user, $workspace);

        $invite = new Invite();
        $invite->setCreatedBy($user->getWorkspaceMember($workspace));
        $invite->setWorkspace($workspace);

        $entityManager->persist($invite);
        $entityManager->flush();

        return $this->json($invite);
    }
}
