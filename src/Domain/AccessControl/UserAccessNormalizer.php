<?php

namespace App\Domain\AccessControl;

use App\Domain\Member\Member;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\StorageItem\StorageItemInterface;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AutoconfigureTag('serializer.normalizer', ["priority" => -10])] // Prevent this normalizer to override subclass normalizers
class UserAccessNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly StorageItemPermissionService $itemPermissionService,
        private readonly WorkspacePermissionService   $workspacePermissionService,
    )
    {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $resource = $context["resource"];
        $workspace = $resource instanceof StorageItemInterface ? $resource->getWorkspace() : $resource;
        $member = $object instanceof User ? $object->getWorkspaceMember($workspace) : $object;

        $data = [];

        if ($resource instanceof StorageItemInterface) {
            $data[Permission::READ] = $this->itemPermissionService->hasItemPermission(
                $member,
                Permission::READ,
                $resource
            );
            $data[Permission::WRITE] = $this->itemPermissionService->hasItemPermission(
                $member,
                Permission::WRITE,
                $resource
            );
            $data[Permission::TRASH] = $this->itemPermissionService->hasItemPermission(
                $member,
                Permission::TRASH,
                $resource
            );
            $data[Permission::DELETE] = $this->itemPermissionService->hasItemPermission(
                $member,
                Permission::DELETE,
                $resource
            );
            $data[Permission::EDIT_PERMISSIONS] = $this->itemPermissionService->hasItemPermission(
                $member,
                Permission::EDIT_PERMISSIONS,
                $resource
            );
        } elseif ($resource instanceof Workspace) {
            $data[Permission::READ] = $this->workspacePermissionService->hasWorkspacePermission(
                $member,
                Permission::READ,
                $resource
            );
            $data[Permission::WRITE] = $this->workspacePermissionService->hasWorkspacePermission(
                $member,
                Permission::WRITE,
                $resource
            );
            $data[Permission::TRASH] = $this->workspacePermissionService->hasWorkspacePermission(
                $member,
                Permission::TRASH,
                $resource
            );
            $data[Permission::DELETE] = $this->workspacePermissionService->hasWorkspacePermission(
                $member,
                Permission::DELETE,
                $resource
            );
            $data[Permission::EDIT_PERMISSIONS] = $this->workspacePermissionService->hasWorkspacePermission(
                $member,
                Permission::EDIT_PERMISSIONS,
                $resource
            );
            $data[Permission::MANAGE_MEMBERS] = $this->workspacePermissionService->hasWorkspacePermission(
                $member,
                Permission::MANAGE_MEMBERS,
                $resource
            );
        }
        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return ($data instanceof User || $data instanceof Member)
            && isset($context['resource']) && (
                $context["resource"] instanceof StorageItemInterface
                || $context["resource"] instanceof Workspace
            );
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            User::class,
            Member::class,
        ];
    }
}
