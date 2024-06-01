<?php

namespace App\Controller\Api\Workspace\Item;

use App\Entity\User;
use App\Entity\Workspace;
use App\Repository\FileRepository;
use App\Security\Permission;
use App\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListRecentFilesController extends AbstractController
{
    #[Route('/api/workspaces/{id}/files/recent', name: 'api_workspace_files_list_recent')]
    public function list_recent_files(
        Workspace $workspace,
        #[CurrentUser] User $user,
        PermissionService $permissionService,
        FileRepository $fileRepository
    ): JsonResponse {
        $permissionService->assertAccess($user, $workspace);
        $member = $user->getWorkspaceMember($workspace);

        $files = $fileRepository->findBy(['workspace' => $workspace], ['updated_at' => 'DESC']);

        $items = [];

        foreach ($files as $file) {
            if (sizeof($items) >= 10) {
                break;
            }
            if ($permissionService->hasItemPermission($member, Permission::READ, $file)) {
                $items[] = $file;
            }
        }

        return $this->json($items, context: [
            "groups" => ["default", "item_details"]
        ]);
    }
}
