<?php

namespace App\Controller\Api\StorageItem;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\User;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Service\StorageItem\StorageItemDeleteService;
use App\Service\StorageItem\StorageItemService;
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
    #[Route('/api/storage-items', name: 'api_storage_items_delete', methods: "DELETE")]
    public function delete(
        Request $request,
        #[CurrentUser] User $user,
        StorageItemService $storageItemService,
        EntityManagerInterface $entityManager,
        StorageItemDeleteService $itemDeleteService,
        PermissionService $permissionService
    ): Response {
        [$files, $folders] = RequestHandler::getTwoRequestParameters($request, "files", "folders");
        $items = [];

        if ($files) {
            foreach ($files as $id) {
                $items[] = $storageItemService->getStorageItem(File::class, $id);
            }
        }
        if ($folders) {
            foreach ($folders as $id) {
                $items[] = $storageItemService->getStorageItem(Folder::class, $id);
            }
        }

        foreach ($items as $item) {
            if (!$item->isInTrash()) {
                throw new BadRequestHttpException(
                    EntityTypeMapper::getNameFromClass($item::class) . " " . $item->getId() . " not in trash"
                );
            }
            $permissionService->assertPermission($user, Permission::DELETE, $item);
            $itemDeleteService->deleteStorageItem($item);
        }

        $entityManager->flush();

        return new Response("done");
    }
}
