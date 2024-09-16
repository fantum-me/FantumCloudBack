<?php

namespace App\Domain\Invite\Controller;

use App\Domain\Invite\Invite;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class InsertInviteController extends AbstractController
{
    #[Route('/api/workspaces/{id}/invites', name: 'api_workspaces_invites_insert', methods: 'POST')]
    public function insert(
        Workspace                    $workspace,
        #[CurrentUser] User          $user,
        EntityManagerInterface       $entityManager,
        WorkspacePermissionService   $workspacePermissionService,
        StorageItemPermissionService $itemPermissionService
    ): JsonResponse
    {
        $workspacePermissionService->assertAccess($user, $workspace);

        $invite = new Invite();
        $invite->setCreatedBy($user->getWorkspaceMember($workspace));
        $invite->setWorkspace($workspace);

        $entityManager->persist($invite);
        $entityManager->flush();

        return $this->json($invite);
    }
}
