<?php

namespace App\Domain\DataTable\Controller\ViewCrud;

use App\Domain\DataTable\Entity\DataTable;
use App\Domain\DataTable\Entity\DataView;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DeleteDataViewController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/databases/{database_id}/views/{id}', name: 'api_workspaces_databases_views_delete', methods: "DELETE")]
    public function update(
        #[CurrentUser] User                        $user,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[MapEntity(id: 'database_id')] DataTable  $dataTable,
        DataView                                   $view,
        EntityManagerInterface                     $entityManager,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $storageItemPermissionService
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemPermissionService->assertPermission($user, Permission::WRITE, $dataTable);

        if ($view->getDataTable()->getViews()->count() === 1) {
            throw new AccessDeniedHttpException('Cannot delete last database view.');
        }

        $entityManager->remove($view);
        $entityManager->flush();

        return new Response("done");
    }
}
