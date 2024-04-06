<?php

namespace App\Controller\Api\Workspace;

use App\Entity\User;
use App\Entity\Workspace;
use App\Service\ObjectMaker\StorageItemObjectService;
use App\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GetWorkspaceStorageStatsController extends AbstractController
{
    #[Route('/api/workspaces/{id}/storage-stats', name: 'api_workspaces_storage_stats', methods: "GET")]
    public function trash(
        Workspace                $workspace,
        #[CurrentUser] User      $user,
        StorageItemObjectService $objectService,
        PermissionService $permissionService
    ): JsonResponse
    {
        $permissionService->assertAccess($user, $workspace);

        [$files, $folders] = [[], []];
        foreach ($workspace->getFiles() as $f) if ($f->isInTrash()) $files[] = $f;
        foreach ($workspace->getFolders() as $f) if ($f->isInTrash()) $folders[] = $f;

        $res = [
            "folders" => [],
            "files" => []
        ];
        foreach ($files as $file) $res["files"][] = $objectService->getFileObject($file);
        foreach ($folders as $folder) $res["folders"][] = $objectService->getFolderObject($folder, false);

        return $this->json($res);
    }
}
