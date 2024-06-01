<?php

namespace App\Serializer;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\StorageItem;
use App\Entity\User;
use App\Security\Permission;
use App\Service\FileSizeService;
use App\Service\PermissionService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class StorageItemNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $normalizer,
        private readonly Security $security,
        private readonly UserAccessNormalizer $userAccessNormalizer,
        private readonly string $workspacePath,
        private readonly PermissionService $permissionService,
        private readonly Filesystem $filesystem
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $user = $this->security->getUser();
        $path = $this->workspacePath . "/" . $object->getPath();

        if ($user instanceof User) {
            $data["access"] = $this->userAccessNormalizer->normalize($user, $format, ["resource" => $object]);
        }

        $data["workspace_id"] = $object->getWorkspace()->getId();
        $data["parent_id"] = $object->getFolder()?->getId();

        $data["size"] = FileSizeService::getItemSize($path);

        if (isset($context["groups"]) && in_array("item_parents", $context["groups"])) {
            $this->addParentsToData($object, $data);
        }

        if ($object instanceof File) {
            $data["type"] = "file";
            $data["has_preview"] = $this->filesystem->exists($this->workspacePath . "/" . $object->getPreviewPath());

            if (str_starts_with($object->getMime(), 'image')) {
                $dimension = getimagesize($path);
                if ($dimension) {
                    $data["width"] = $dimension[0];
                    $data["height"] = $dimension[1];
                }
            }
        } elseif ($object instanceof Folder) {
            $data["type"] = "folder";
            $data["is_root"] = $object->getFolder() === null;

            if (isset($context["groups"]) && in_array("folder_children", $context["groups"])) {
                $this->addChildrenToData(
                    $object,
                    $data,
                    $format,
                    ["groups" => ["default", "item_details"]]
                );
            }
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof StorageItem;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            StorageItem::class => true,
        ];
    }

    private function addChildrenToData(Folder $folder, array &$data, ?string $format, array $context = []): void
    {
        $data["files"] = [];
        $data["folders"] = [];

        $user = $this->security->getUser();
        $member = $user?->getWorkspaceMember($folder->getWorkspace());
        if ($user && !$member) {
            return;
        }

        foreach ($folder->getItems() as $item) {
            if (!$item->isInTrash()) {
                if (!$user || $this->permissionService->hasItemPermission($member, Permission::READ, $item)) {
                    $data["items"][] = $this->normalize($item, $format, $context);
                }
            }
        }
    }

    private function addParentsToData(StorageItem $item, array &$data): void
    {
        if ($item->isInTrash()) {
            $data["parents"] = [["id" => "trash", "name" => "Trash"]];
            return;
        }

        $parents = [];
        $parent = $item->getFolder();

        while ($parent) {
            $parents[] = [
                "id" => $parent->getId(),
                "name" => $parent->getName(),
                "is_root" => $item->getWorkspace()->getRootFolder() === $parent
            ];

            if ($parent->isInTrash()) {
                $parents[] = ["id" => "trash", "name" => "Trash"];
                break;
            }

            $parent = $parent->getFolder();
        }

        $data["parents"] = array_reverse($parents);
    }
}
