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
use App\Service\PositionEntityService;
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

class InsertTableFieldController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/databases/{id}/fields', name: 'api_workspaces_databases_fields_insert', methods: "POST")]
    public function insert(
        Request                                    $request,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User                        $user,
        DataTable                                  $dataTable,
        PositionEntityService $positionEntityService,
        EntityManagerInterface                     $entityManager,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $storageItemPermissionService,
        ValidatorInterface                         $validator
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemPermissionService->assertPermission($user, Permission::WRITE, $dataTable);

        $field = new TableField();
        $dataTable->addField($field);
        $positionEntityService->setEntityPosition($field, "dataTable", $field->getDataTable()->getFields()->count() - 1);

        $name = RequestHandler::getRequestParameter($request, "name", true);
        $field->setName($name);

        $type = RequestHandler::getRequestParameter($request, "type", true);
        if ($fieldType = TableFieldType::tryFrom($type)) $field->setType($fieldType);
        else throw new BadRequestHttpException("invalid type $type");

        if ($type === TableFieldType::SelectType) {
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
