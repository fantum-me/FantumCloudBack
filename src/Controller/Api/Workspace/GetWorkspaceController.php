<?php

namespace App\Controller\Api\Workspace;

use App\Entity\User;
use App\Entity\Workspace;
use App\Service\ObjectMaker\WorkspaceObjectService;
use App\Service\PermissionService;
use App\Utils\RequestHandler;
use phpDocumentor\Reflection\Types\Array_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GetWorkspaceController extends AbstractController
{
    #[Route('/api/workspaces/{id}', name: 'api_workspaces_get', methods: "GET")]
    public function get(
        Request $request,
        Workspace $workspace,
        #[CurrentUser] User $user,
        WorkspaceObjectService $workspaceObjectService,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertAccess($user, $workspace);

        $scopes = RequestHandler::getRequestParameter($request, "scopes"); // optional
        if ($scopes !== null && !is_array($scopes)) {
            $scopes = [$scopes];
        }

        return $this->json($workspaceObjectService->getWorkspaceObject($workspace, $scopes));
    }
}
