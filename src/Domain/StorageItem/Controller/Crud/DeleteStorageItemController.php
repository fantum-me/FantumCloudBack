<?php

namespace App\Domain\StorageItem\Controller\Crud;

use App\Domain\StorageItem\Service\StorageItemDeleteService;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\StorageItem\Service\StorageItemService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use App\Utils\EntityTypeMapper;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DeleteStorageItemController extends AbstractController
{
    #[Route('/api/workspaces/{id}/items', name: 'api_workspaces_items_delete', methods: "DELETE")]
    public function delete(
        Request                      $request,
        Workspace                    $workspace,
        #[CurrentUser] User          $user,
        StorageItemService           $storageItemService,
        EntityManagerInterface       $entityManager,
        StorageItemDeleteService     $itemDeleteService,
        WorkspacePermissionService   $workspacePermissionService,
        StorageItemPermissionService $itemPermissionService
    ): Response
    {
        $workspacePermissionService->assertAccess($user, $workspace);

        $itemIds = RequestHandler::getRequestParameter($request, "items", true);

        foreach ($itemIds as $id) {
            $item = $storageItemService->getStorageItem($id);
            $storageItemService->assertInWorkspace($workspace, $item);
            $itemPermissionService->assertPermission($user, Permission::DELETE, $item);

            if (!$item->isInTrash()) {
                throw new BadRequestHttpException(
                    EntityTypeMapper::getNameFromClass($item::class) . " " . $item->getId() . " not in trash"
                );
            }

            $itemDeleteService->deleteStorageItem($item);
        }

        $entityManager->flush();

        return new Response("done");
    }
}
