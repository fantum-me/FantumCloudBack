<?php

namespace App\Domain\StorageItem\Controller\Crud;

use App\Domain\StorageItem\Service\StorageItemMoveService;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\StorageItem\Service\StorageItemService;
use App\Domain\StorageItem\Service\StorageItemTrashService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use App\Utils\EntityTypeMapper;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ModifyStorageItemController extends AbstractController
{
    #[Route('/api/workspaces/{id}/items', name: 'api_workspaces_items_modify', methods: "PATCH")]
    public function modify(
        Request                      $request,
        Workspace                    $workspace,
        #[CurrentUser] User          $user,
        StorageItemService           $storageItemService,
        EntityManagerInterface       $entityManager,
        StorageItemMoveService       $moveService,
        WorkspacePermissionService   $workspacePermissionService,
        StorageItemPermissionService $itemPermissionService
    ): JsonResponse
    {
        $workspacePermissionService->assertAccess($user, $workspace);

        $itemIds = RequestHandler::getRequestParameter($request, "items", true);

        $items = [];
        foreach ($itemIds as $id) {
            $item = $storageItemService->getStorageItem($id);
            $storageItemService->assertInWorkspace($workspace, $item);
            $items[] = $item;
        }

        $name = RequestHandler::getRequestParameter($request, "name");
        $inTrash = RequestHandler::getRequestParameter($request, "in_trash");

        $parentId = RequestHandler::getRequestParameter($request, "parent_id");
        if ($parentId) {
            $parent = $storageItemService->getStorageItem($parentId);
            $parent = $storageItemService->assertFolder($parent);
            $storageItemService->assertInWorkspace($workspace, $parent);
            $itemPermissionService->assertPermission($user, Permission::WRITE, $parent);
            if ($parent->isInTrash()) {
                throw new BadRequestHttpException("parent folder is in trash");
            }
        }

        foreach ($items as $item) {
            $item->updateVersion();
            if ($name) {
                $itemPermissionService->assertPermission($user, Permission::WRITE, $item);
                $item->setName($name);
            }
            if ($parentId && $parent) {
                if ($item->isInTrash()) {
                    throw new BadRequestHttpException(
                        EntityTypeMapper::getNameFromClass($item::class) . " " . $item->getId() . " is in trash"
                    );
                }
                if ($item->getWorkspace() !== $parent->getWorkspace()) {
                    throw new BadRequestHttpException(
                        EntityTypeMapper::getNameFromClass($item::class) . " " . $item->getId() . " is not in same workspace"
                    );
                }
                $itemPermissionService->assertPermission($user, Permission::WRITE, $item);
                $moveService->moveStorageItem($item, $parent);
            }
            if (isset($inTrash)) {
                if ($inTrash) {
                    $itemPermissionService->assertPermission($user, Permission::TRASH, $item);
                    StorageItemTrashService::trashItem($item);
                } else {
                    $itemPermissionService->assertPermission($user, Permission::WRITE, $item);
                    $item->setInTrash(false);
                }
            }
        }

        if ($parentId) {
            $parent?->updateVersion();
        }
        $entityManager->flush();

        return $this->json($items, 200, [], [
            "groups" => ["default", "item_details"]
        ]);
    }
}
