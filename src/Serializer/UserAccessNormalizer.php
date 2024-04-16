<?php

namespace App\Serializer;

use App\Entity\Interface\StorageItemInterface;
use App\Entity\User;
use App\Entity\Workspace;
use App\Security\Permission;
use App\Service\PermissionService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AutoconfigureTag('serializer.normalizer', ["priority" => -10])] // Prevent this normalizer to override subclass normalizers
class UserAccessNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly PermissionService $permissionService
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $resource = $context["resource"];

        $data = [];

        if ($resource instanceof StorageItemInterface) {
            $data[Permission::READ] = $this->permissionService->hasItemPermission($object, Permission::READ, $resource);
            $data[Permission::WRITE] = $this->permissionService->hasItemPermission(
                $object,
                Permission::WRITE,
                $resource
            );
            $data[Permission::TRASH] = $this->permissionService->hasItemPermission(
                $object,
                Permission::TRASH,
                $resource
            );
            $data[Permission::DELETE] = $this->permissionService->hasItemPermission(
                $object,
                Permission::DELETE,
                $resource
            );
            $data[Permission::EDIT_PERMISSIONS] = $this->permissionService->hasItemPermission(
                $object,
                Permission::EDIT_PERMISSIONS,
                $resource
            );
        } elseif ($resource instanceof Workspace) {
            $data[Permission::READ] = $this->permissionService->hasWorkspacePermission(
                $object,
                Permission::READ,
                $resource
            );
            $data[Permission::WRITE] = $this->permissionService->hasWorkspacePermission(
                $object,
                Permission::WRITE,
                $resource
            );
            $data[Permission::TRASH] = $this->permissionService->hasWorkspacePermission(
                $object,
                Permission::TRASH,
                $resource
            );
            $data[Permission::DELETE] = $this->permissionService->hasWorkspacePermission(
                $object,
                Permission::DELETE,
                $resource
            );
            $data[Permission::EDIT_PERMISSIONS] = $this->permissionService->hasWorkspacePermission(
                $object,
                Permission::EDIT_PERMISSIONS,
                $resource
            );
        }
        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof User
            && isset($context['resource']) && (
                $context["resource"] instanceof StorageItemInterface
                || $context["resource"] instanceof Workspace
            );
    }
}
