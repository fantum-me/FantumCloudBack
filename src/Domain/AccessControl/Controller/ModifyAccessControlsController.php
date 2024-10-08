<?php

namespace App\Domain\AccessControl\Controller;

use App\Domain\AccessControl\AccessControl;
use App\Domain\AccessControl\UserAccessNormalizer;
use App\Domain\Role\RoleRepository;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\StorageItem\Service\StorageItemService;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ModifyAccessControlsController extends AbstractController
{
    #[Route("/api/workspaces/{workspace_id}/items/{id}/access-controls", name: "api_workspaces_items_modify_access_controls", methods: "PATCH")]
    public function modify_access_controls(
        Request                                    $request,
        #[MapEntity(id: 'workspace_id')] Workspace $workspace,
        #[CurrentUser] User                        $user,
        string                                     $id,
        StorageItemService                         $storageItemService,
        WorkspacePermissionService                 $workspacePermissionService,
        StorageItemPermissionService               $itemPermissionService,
        RoleRepository                             $roleRepository,
        EntityManagerInterface                     $entityManager,
        LoggerInterface                            $logger,
        UserAccessNormalizer                       $userAccessNormalizer
    ): JsonResponse
    {
        $workspacePermissionService->assertAccess($user, $workspace);
        $member = $user->getWorkspaceMember($workspace);
        $memberPosition = $member->getRoles()->get(0)->getPosition();

        $access = $userAccessNormalizer->normalize($member, context: ["resource" => $workspace]);

        $item = $storageItemService->getStorageItem($id);
        $storageItemService->assertInWorkspace($workspace, $item);
        $itemPermissionService->assertPermission($user, Permission::EDIT_PERMISSIONS, $item);

        $accessControls = RequestHandler::getRequestParameter($request, "access_controls", true);
        $roleIds = array_column($accessControls, "role_id");

        $existingRoleIds = [];
        foreach ($item->getAccessControls() as $accessControl) {
            $roleId = $accessControl->getRole()->getId();
            $existingRoleIds[] = $roleId;
            $key = array_search($roleId, $roleIds);
            $logger->debug($key);
            $canBeModified = $member->isOwner() || $memberPosition >= $accessControl->getRole()->getPosition();

            if ($key === false) {
                if (!$canBeModified) {
                    continue;
                }
                $item->removeAccessControl($accessControl);
                $entityManager->remove($accessControl);
            } else {
                if ($canBeModified) {
                    $provided = $accessControls[$key];
                    foreach ($provided["permissions"] as $permission => $value) {
                        if ($access[$permission]) {
                            $accessControl->setPermission($permission, $value);
                        }
                    }
                }
                array_splice($accessControls, $key, 1);
                $roleIds = array_column($accessControls, "role_id");
            }
        }

        foreach ($accessControls as $provided) {
            if (isset($provided["role_id"]) && !in_array($provided["role_id"], $existingRoleIds)) {
                $role = $roleRepository->find($provided["role_id"]);
                if (!$role) {
                    throw new BadRequestHttpException("Could not find role with id " . $provided["role_id"]);
                }

                if ($member->isOwner() || $memberPosition >= $role->getPosition()) {
                    $accessControl = new AccessControl();
                    $item->addAccessControl($accessControl);
                    $role->addAccessControl($accessControl);
                    $logger->critical("adding " . $role->getName());

                    foreach ($provided["permissions"] as $permission => $value) {
                        if ($access[$permission]) {
                            $accessControl->setPermission($permission, $value);
                        }
                    }

                    $entityManager->persist($accessControl);
                }
            }
        }

        $entityManager->flush();

        return $this->json($item, context: [
            "groups" => ["default", "item_details"]
        ]);
    }
}
