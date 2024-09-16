<?php

namespace App\Domain\Workspace\Controller;

use App\Domain\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListWorkspacesController extends AbstractController
{
    #[Route('/api/workspaces', name: 'api_workspaces_list', methods: "GET")]
    public function list(
        #[CurrentUser] User $user
    ): JsonResponse
    {
        $workspaces = [];
        foreach ($user->getRelatedMembers() as $member) {
            $workspaces[] = $member->getWorkspace();
        }
        return $this->json($workspaces);
    }
}
