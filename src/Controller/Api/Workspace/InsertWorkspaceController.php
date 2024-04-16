<?php

namespace App\Controller\Api\Workspace;

use App\Entity\User;
use App\Factory\WorkspaceFactory;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class InsertWorkspaceController extends AbstractController
{
    #[Route('/api/workspaces', name: 'api_workspaces_insert', methods: "POST")]
    public function insert(
        Request $request,
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
        WorkspaceFactory $workspaceFactory
    ): JsonResponse {
        $name = RequestHandler::getRequestParameter($request, "name", true);
        $workspace = $workspaceFactory->createWorkspace($user, $name, $this->getParameter("workspace_default_quota"));
        $entityManager->flush();
        return $this->json($workspace, 200, [], [
            "groups" => ["default", "workspace_details"]
        ]);
    }
}
