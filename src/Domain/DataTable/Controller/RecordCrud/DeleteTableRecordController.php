<?php

namespace App\Domain\DataTable\Controller\RecordCrud;

use App\Domain\DataTable\Entity\DataTable;
use App\Domain\DataTable\Entity\TableRecord;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DeleteTableRecordController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/databases/{database_id}/records/{id}', name: 'api_workspaces_databases_records_delete', methods: "DELETE")]
    public function update(
        #[CurrentUser] User                        $user,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[MapEntity(id: 'database_id')] DataTable  $dataTable,
        TableRecord                                $record,
        EntityManagerInterface                     $entityManager,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $storageItemPermissionService
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemPermissionService->assertPermission($user, Permission::WRITE, $dataTable);

        $entityManager->remove($record);
        $entityManager->flush();

        return new Response("done");
    }
}
