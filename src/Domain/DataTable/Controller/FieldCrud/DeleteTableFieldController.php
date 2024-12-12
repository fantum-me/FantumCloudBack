<?php

namespace App\Domain\DataTable\Controller\FieldCrud;

use App\Domain\DataTable\Entity\DataTable;
use App\Domain\DataTable\Entity\TableField;
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

class DeleteTableFieldController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/databases/{database_id}/fields/{id}', name: 'api_workspaces_databases_fields_delete', methods: "DELETE")]
    public function update(
        #[CurrentUser] User                        $user,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[MapEntity(id: 'database_id')] DataTable  $dataTable,
        TableField                                 $field,
        EntityManagerInterface                     $entityManager,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $storageItemPermissionService
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemPermissionService->assertPermission($user, Permission::WRITE, $dataTable);

        if ($field->isTitle()) throw new AccessDeniedHttpException('Cannot delete title field.');

        foreach ($field->getDataTable()->getViews()->toArray() as $view) {
            $settings = $view->getFieldSettings();
            if (array_key_exists($field->getId()->toRfc4122(), $settings)) {
                unset($settings[$field->getId()->toRfc4122()]);
                $view->setFieldSettings($settings);
            }
        }

        $entityManager->remove($field);
        $entityManager->flush();

        return new Response("done");
    }
}
