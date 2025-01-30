<?php

namespace App\Domain\DataTable\Controller\FieldCrud;

use App\Domain\DataTable\Entity\DataTable;
use App\Domain\DataTable\Entity\TableField;
use App\Domain\DataTable\Service\TableFieldTypeService;
use App\Domain\DataTable\TableFieldType;
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

class UpdateTableFieldController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/databases/{database_id}/fields/{id}', name: 'api_workspaces_databases_fields_update', methods: "PATCH")]
    public function update(
        Request                                    $request,
        #[CurrentUser] User                        $user,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[MapEntity(id: 'database_id')] DataTable  $dataTable,
        TableField                                 $field,
        EntityManagerInterface                     $entityManager,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $storageItemPermissionService,
        ValidatorInterface                         $validator
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemPermissionService->assertPermission($user, Permission::WRITE, $dataTable);

        if ($name = RequestHandler::getRequestParameter($request, "name")) {
            $field->setName($name);
        }

        if ($field->getType() === TableFieldType::SelectType || $field->getType() === TableFieldType::MultiselectType) {
            $options = RequestHandler::getRequestParameter($request, "options");
            if ($options) {
                if (TableFieldTypeService::isValidOptionsArray($options)) $field->setOptions($options);
                else throw new BadRequestHttpException("invalid options array format");
            } else $field->setOptions([]);
        }

        if (count($errors = $validator->validate($field)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $entityManager->persist($field);
        $entityManager->flush();

        return $this->json($field, 200, [], [
            'groups' => ["default", "datatable_details"]
        ]);
    }
}
