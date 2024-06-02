<?php

namespace App\Controller\Api\Workspace\Item;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\User;
use App\Entity\Workspace;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Service\StorageItem\StorageItemMoveService;
use App\Service\StorageItem\StorageItemService;
use App\Service\StorageItem\StorageItemTrashService;
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
        Request $request,
        Workspace $workspace,
        #[CurrentUser] User $user,
        StorageItemService $storageItemService,
        EntityManagerInterface $entityManager,
        StorageItemMoveService $moveService,
        PermissionService $permissionService
    ): JsonResponse {
        $permissionService->assertAccess($user, $workspace);

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
            $permissionService->assertPermission($user, Permission::WRITE, $parent);
            if ($parent->isInTrash()) {
                throw new BadRequestHttpException("parent folder is in trash");
            }
        }

        foreach ($items as $item) {
            $item->updateVersion();
            if ($name) {
                $permissionService->assertPermission($user, Permission::WRITE, $item);
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
                        EntityTypeMapper::getNameFromClass($item::class) . " " . $item->getId(
                        ) . " is not in same workspace"
                    );
                }
                $permissionService->assertPermission($user, Permission::WRITE, $item);
                $moveService->moveStorageItem($item, $parent);
            }
            if (isset($inTrash)) {
                if ($inTrash) {
                    $permissionService->assertPermission($user, Permission::TRASH, $item);
                    StorageItemTrashService::trashItem($item);
                } else {
                    $permissionService->assertPermission($user, Permission::WRITE, $item);
                    $item->setInTrash(false);
                }
            }
        }

        if ($parent) {
            $parent->updateVersion();
        }
        $entityManager->flush();

        return $this->json($items, 200, [], [
            "groups" => ["default", "item_details"]
        ]);
    }
}
