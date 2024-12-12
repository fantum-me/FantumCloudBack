<?php

namespace App\Domain\DataTable\Controller\ViewCrud;

use App\Domain\DataTable\DataTableViewType;
use App\Domain\DataTable\Entity\DataTable;
use App\Domain\DataTable\Entity\DataView;
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

class InsertDataViewController extends AbstractController
{
    #[Route('/api/workspaces/{workspace_id}/databases/{id}/views', name: 'api_workspaces_databases_views_insert', methods: "POST")]
    public function insert(
        Request                                    $request,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User                        $user,
        DataTable                                  $dataTable,
        EntityManagerInterface                     $entityManager,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $storageItemPermissionService,
        ValidatorInterface                         $validator
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $storageItemPermissionService->assertPermission($user, Permission::WRITE, $dataTable);

        $view = new DataView();
        $view->setCreatedBy($user->getWorkspaceMember($workspace));
        $dataTable->addView($view);

        $name = RequestHandler::getRequestParameter($request, "name", true);
        $view->setName($name);

        $type = RequestHandler::getRequestParameter($request, "type", true);
        if ($viewType = DataTableViewType::tryFrom($type)) $view->setType($viewType);
        else throw new BadRequestHttpException("invalid type $type");

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
