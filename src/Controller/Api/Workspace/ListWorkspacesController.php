<?php

namespace App\Controller\Api\Workspace;

use App\Entity\User;
use App\Service\ObjectMaker\WorkspaceObjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListWorkspacesController extends AbstractController
{
    #[Route('/api/workspaces', name: 'api_workspaces_list', methods: "GET")]
    public function list(
        #[CurrentUser] User    $user,
        WorkspaceObjectService $workspaceObjectService
    ): JsonResponse
    {
        $workspaces = [];
        foreach ($user->getRelatedMembers() as $member) {
            $workspaces[] = $workspaceObjectService->getWorkspaceObject($member->getWorkspace());
        }
        return $this->json($workspaces);
    }
}
