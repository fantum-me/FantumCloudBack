<?php

namespace App\Domain\DataTable\Controller;

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

class InsertTableRecordController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/databases/{id}/records', name: 'api_workspaces_databases_records_insert', methods: "POST")]
    public function insert(
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User                        $user,
        DataTable                                  $dataTable,
        EntityManagerInterface                     $entityManager,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $storageItemPermissionService,
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemPermissionService->assertPermission($user, Permission::WRITE, $dataTable);

        $record = new TableRecord();
        $record->setDataTable($dataTable);

        $entityManager->persist($record);
        $entityManager->flush();

        return $this->json($record, 200, [], [
            'groups' => ["default", "datatable_details"]
        ]);
    }
}
