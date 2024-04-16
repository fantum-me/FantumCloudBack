<?php

namespace App\Serializer;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\User;
use App\Security\Permission;
use App\Service\FileSizeService;
use App\Service\PermissionService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class FolderNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $normalizer,
        private readonly Security $security,
        private readonly UserAccessNormalizer $userAccessNormalizer,
        private readonly string $workspacePath,
        private readonly PermissionService $permissionService,
        private readonly FileNormalizer $fileNormalizer
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $data["access"] = $this->userAccessNormalizer->normalize($user, $format, ["resource" => $object]);
        }
        $data["workspace_id"] = $object->getWorkspace()->getId();

        $data["is_root"] = $object->getFolder() === null;
        $data["size"] = FileSizeService::getFolderSize($this->workspacePath . "/" . $object->getPath());

        if (isset($context["groups"]) && in_array("folder_parents", $context["groups"])) {
             $this->addParentsToData($object, $data);
        }

        if (isset($context["groups"]) && in_array("folder_children", $context["groups"])) {
            $this->addChildrenToData($object, $data, $format, $context);
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Folder;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Folder::class => true,
        ];
    }

    private function addChildrenToData(Folder $folder, array &$data, ?string $format, array $context = []): void
    {
        $user = $this->security->getUser();

        $data["files"] = [];
        foreach ($folder->getFiles() as $file) {
            if (!$file->isInTrash()) {
                if (!$user || $this->permissionService->hasItemPermission($user, Permission::READ, $file)) {
                    $data["files"][] = $this->fileNormalizer->normalize($file, $format, $context);
                }
            }
        }

        $data["folders"] = [];
        foreach ($folder->getFolders() as $folder) {
            if (!$folder->isInTrash()) {
                if (!$user || $this->permissionService->hasItemPermission($user, Permission::READ, $folder)) {
                    $data["folders"][] = $this->normalize($folder, $format, $context);
                }
            }
        }
    }

    private function addParentsToData(Folder $folder, array &$data): void
    {
        $parents = [];
        $parent = $folder->getFolder();

        while ($parent) {
            $parents[] = [
                "id" => $parent->getId(),
                "name" => $parent->getName(),
                "is_root" => $folder->getWorkspace()->getRootFolder() === $parent
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
