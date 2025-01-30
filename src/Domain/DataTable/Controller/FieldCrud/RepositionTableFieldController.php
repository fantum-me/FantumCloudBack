<?php

namespace App\Domain\DataTable\Controller\FieldCrud;

use App\Domain\DataTable\Entity\DataTable;
use App\Domain\DataTable\Entity\TableField;
use App\Domain\DataTable\Publisher\TableFieldPublisher;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use App\Service\PositionEntityService;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class RepositionTableFieldController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/databases/{database_id}/fields/{id}/reposition',
        name: 'api_workspaces_databases_fields_reposition', methods: "PATCH")]
    public function reposition(
        Request                                    $request,
        #[CurrentUser] User                        $user,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[MapEntity(id: 'database_id')] DataTable  $dataTable,
        TableField                                 $field,
        PositionEntityService                      $positionEntityService,
        EntityManagerInterface                     $entityManager,
        TableFieldPublisher                        $publisher,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $storageItemPermissionService,
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemPermissionService->assertPermission($user, Permission::WRITE, $dataTable);

        $position = RequestHandler::getRequestParameter($request, 'position', true);
        $positionEntityService->setEntityPosition($field, "dataTable", $position);

        $entityManager->flush();
        $publisher->publishNewPosition($field);

        return $this->json($field, 200, [], [
            'groups' => ["default", "datatable_details"]
        ]);
    }
}
