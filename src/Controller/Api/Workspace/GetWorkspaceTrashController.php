<?php

namespace App\Controller\Api\Workspace;

use App\Entity\User;
use App\Entity\Workspace;
use App\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GetWorkspaceTrashController extends AbstractController
{
    #[Route('/api/workspaces/{id}/trash', name: 'api_workspaces_trash', methods: "GET")]
    public function trash(
        Workspace $workspace,
        #[CurrentUser] User $user,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertAccess($user, $workspace);

        [$files, $folders] = [[], []];
        foreach ($workspace->getFiles() as $f) {
            if ($f->isInTrash()) {
                $files[] = $f;
            }
        }
        foreach ($workspace->getFolders() as $f) {
            if ($f->isInTrash()) {
                $folders[] = $f;
            }
        }

        $res = [
            "files" => $files,
            "folders" => $folders
        ];

        return $this->json($res, 200, [], [
            "groups" => ["default", "file_details", "folder_details", "folder_parents"]
        ]);
    }
}
