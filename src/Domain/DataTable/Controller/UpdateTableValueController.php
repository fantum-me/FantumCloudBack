<?php

namespace App\Domain\DataTable\Controller;

use App\Domain\DataTable\Entity\DataTable;
use App\Domain\DataTable\Entity\TableField;
use App\Domain\DataTable\Entity\TableRecord;
use App\Domain\DataTable\Entity\TableValue;
use App\Domain\DataTable\Publisher\TableValuePublisher;
use App\Domain\DataTable\Repository\TableValueRepository;
use App\Domain\DataTable\Service\TableFieldTypeService;
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

class UpdateTableValueController extends AbstractController
{
    #[Route(
        '/api/workspaces/{workspace_id}/databases/{database_id}/fields/{field_id}/records/{record_id}',
        name: 'api_workspaces_databases_values_update',
        methods: "PATCH"
    )]
    public function update(
        Request                                    $request,
        #[CurrentUser] User                        $user,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[MapEntity(id: 'database_id')] DataTable  $dataTable,
        #[MapEntity(id: 'field_id')] TableField    $field,
        #[MapEntity(id: 'record_id')] TableRecord  $record,
        TableValueRepository                       $valueRepository,
        EntityManagerInterface                     $entityManager,
        TableValuePublisher                        $publisher,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $storageItemPermissionService,
        ValidatorInterface                         $validator
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemPermissionService->assertPermission($user, Permission::WRITE, $dataTable);

        $value = RequestHandler::getRequestParameter($request, "value", true);
        if (!TableFieldTypeService::isValueValid($field, $value)) {
            throw new BadRequestHttpException("value is not valid");
        }

        $tableValue = $valueRepository->findOneBy(["relatedField" => $field, "record" => $record]);
        $updated = true;

        if ($tableValue) {
            if (!TableFieldTypeService::isDefaultEmptyValue($field, $value)) $tableValue->setValue($value);
            else $entityManager->remove($tableValue);
        } elseif (!TableFieldTypeService::isDefaultEmptyValue($field, $value)) {
            $tableValue = new TableValue();
            $tableValue->setRelatedField($field);
            $tableValue->setRecord($record);
            $tableValue->setValue($value);
            $entityManager->persist($tableValue);
        } else $updated = false;

        if ($updated) {
            if (count($errors = $validator->validate($tableValue)) > 0) {
                throw new BadRequestHttpException($errors->get(0)->getMessage());
            }
            $entityManager->flush();
            $publisher->sendTableValueUpdate($field, $record, $value);
        }

        return $this->json($tableValue, 200, [], [
            'groups' => ["default", "datatable_details"]
        ]);
    }
}
