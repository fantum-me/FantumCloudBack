<?php

namespace App\Domain\DataTable\Controller;

use App\Domain\DataTable\DataTableViewType;
use App\Domain\DataTable\Entity\DataTable;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UpdateDatabaseController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/databases/{id}', name: 'api_workspaces_databases_update', methods: "PATCH")]
    public function update(
        Request                                    $request,
        #[CurrentUser] User                        $user,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        DataTable                                  $dataTable,
        EntityManagerInterface                     $entityManager,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $storageItemPermissionService
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemPermissionService->assertPermission($user, Permission::WRITE, $dataTable);

        $views = RequestHandler::getRequestParameter($request, "views", true);

        if (!is_array($views)) throw new BadRequestHttpException("views must be an array.");
        if (sizeof($views) == 0) throw new BadRequestHttpException("views must not be empty.");

        $views = array_map(function ($view) {
            if ($v = DataTableViewType::tryFrom($view)) return $v;
            else throw new BadRequestHttpException("Invalid view $view.");
        }, $views);

        $dataTable->setViews(...$views);

        $entityManager->persist($dataTable);
        $entityManager->flush();

        return $this->json($dataTable, 200, [], [
            'groups' => ["default", "datatable_details"]
        ]);
    }
}
