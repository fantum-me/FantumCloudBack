<?php

namespace App\Domain\DataTable\Controller;

use App\Domain\DataTable\Entity\DataTable;
use App\Domain\DataTable\Entity\TableRecord;
use App\Domain\DataTable\Entity\TableValue;
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

class UpdateTableRecordController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/databases/{database_id}/records/{id}', name: 'api_workspaces_databases_records_update', methods: "PATCH")]
    public function update(
        Request                                    $request,
        #[CurrentUser] User                        $user,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[MapEntity(id: 'database_id')] DataTable  $dataTable,
        TableRecord                                $record,
        EntityManagerInterface                     $entityManager,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $storageItemPermissionService,
        ValidatorInterface                         $validator
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemPermissionService->assertPermission($user, Permission::WRITE, $dataTable);

        $values = RequestHandler::getRequestParameter($request, "values", true);

        foreach ($record->getRelatedValues()->toArray() as $value) {
            $field = $value->getRelatedField();
            $fieldId = $field->getId()->toRfc4122();
            if (array_key_exists($fieldId, $values)) {
                if (TableFieldTypeService::isDefaultEmptyValue($field, $value->getValue())) {
                    $entityManager->remove($value);
                } elseif (TableFieldTypeService::isValueValid($field, $value->getValue())) {
                    if ($value->getValue() !== ($newValue = $values[$fieldId])) {
                        $value->setValue($newValue);
                        $entityManager->persist($value);
                    }
                } else {
                    throw new BadRequestHttpException(sprintf(
                        "invalid value for %s field %s (%s)",
                        $field->getType()->value, $field->getName(), $fieldId
                    ));
                }
                unset($values[$value->getId()->toRfc4122()]);
            }
        }

        $fields = $dataTable->getFields()->toArray();
        $fields = array_combine(array_map(fn($f) => $f->getId(), $fields), $fields);

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $fields)) {
                $field = $fields[$key];
                if (TableFieldTypeService::isValueValid($field, $value)) {
                    if (!TableFieldTypeService::isDefaultEmptyValue($field, $value)) {
                        $tableValue = new TableValue();
                        $tableValue->setValue($value)->setRelatedField($field);
                        $record->addRelatedValue($tableValue);

                        if (count($errors = $validator->validate($tableValue)) > 0) {
                            throw new BadRequestHttpException($errors->get(0)->getMessage());
                        }

                        $entityManager->persist($tableValue);
                    }
                } else {
                    throw new BadRequestHttpException(sprintf(
                        "invalid value for %s field %s (%s)",
                        $field->getType()->value, $field->getName(), $field->getId()
                    ));
                }
            } else {
                throw new BadRequestHttpException("field $key does not exist");
            }
        }

        if (count($errors = $validator->validate($record)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $entityManager->persist($record);
        $entityManager->flush();

        return $this->json($record, 200, [], [
            'groups' => ["default", "datatable_details"]
        ]);
    }
}
