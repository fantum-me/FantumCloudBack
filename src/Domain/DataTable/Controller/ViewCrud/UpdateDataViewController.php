<?php

namespace App\Domain\DataTable\Controller\ViewCrud;

use App\Domain\DataTable\Entity\DataTable;
use App\Domain\DataTable\Entity\DataView;
use App\Domain\DataTable\Service\DataViewSettingsService;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateDataViewController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/databases/{database_id}/views/{id}', name: 'api_workspaces_databases_views_update', methods: "PATCH")]
    public function update(
        Request                                    $request,
        #[CurrentUser] User                        $user,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[MapEntity(id: 'database_id')] DataTable  $dataTable,
        DataView                                   $view,
        EntityManagerInterface                     $entityManager,
        DataViewSettingsService                    $dataViewSettingsService,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $storageItemPermissionService,
        ValidatorInterface                         $validator
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemPermissionService->assertPermission($user, Permission::WRITE, $dataTable);

        if ($name = RequestHandler::getRequestParameter($request, "name")) {
            $view->setName($name);
        }

        if ($fieldSettings = RequestHandler::getRequestParameter($request, "field_settings")) {
            if (is_array($fieldSettings) && $dataViewSettingsService->validateFieldSettings($view, $fieldSettings)) {
                $view->setFieldSettings($fieldSettings);
            } else throw new BadRequestHttpException("field_settings is invalid");
        }

        if (count($errors = $validator->validate($view)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $entityManager->persist($view);
        $entityManager->flush();

        return $this->json($view, 200, [], [
            'groups' => ["default", "datatable_details"]
        ]);
    }
}
