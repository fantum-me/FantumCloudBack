<?php

namespace App\Service\ObjectMaker;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\Interface\StorageItemInterface;
use App\Security\Permission;
use App\Service\FileSizeService;
use App\Service\PermissionService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;

class StorageItemObjectService
{
    public function __construct(
        private readonly string $workspacePath,
        private readonly Security $security,
        private readonly Filesystem $filesystem,
        private readonly PermissionService $permissionService,
        private readonly MemberObjectService $userObjectService,
        private readonly PermissionManagerObjectService $permissionManagerObjectService,
        private readonly UserAccessObjectService $userAccessObjectService
    ) {
    }

    private function getStorageItemObject(StorageItemInterface $item): array
    {
        $object = [
            "id" => $item->getId(),
            "name" => $item->getName(),
            "created_at" => $item->getCreatedAt()->getTimestamp(),
            "updated_at" => $item->getUpdatedAt()->getTimestamp(),
            "version" => $item->getVersion(),
            "in_trash" => $item->isInTrash(true),
            "owner" => $this->userObjectService->getMemberObject($item->getWorkspace()->getOwner()),
            "access" => $this->userAccessObjectService->getItemAccessObject($this->security->getUser(), $item),
            "access_controls" => [],
            "workspace_id" => $item->getWorkspace()->getId()
        ];

        foreach ($item->getAccessControls() as $accessControl) {
            $object["access_controls"][] = $this->permissionManagerObjectService->getPermissionManagerObject(
                $accessControl
            );
        }

        return $object;
    }

    public function getFileObject(File $file): array
    {
        $hasPreview = $this->filesystem->exists($this->workspacePath . "/" . $file->getPreviewPath());

        $object = $this->getStorageItemObject($file);
        $object["ext"] = $file->getExt();
        $object["mime"] = $file->getType();
        $object["has_preview"] = $hasPreview;
        $object["size"] = FileSizeService::getFileSize($this->workspacePath . "/" . $file->getPath());
        $object["parent_id"] = $file->getFolder()->getId();
        return $object;
    }

    public function getFolderObject(Folder $folder, $withContent = true, $withParents = true): array
    {
        $object = $this->getStorageItemObject($folder);

        $object["is_root"] = $folder->getFolder() === null;
        $path = $this->workspacePath . "/" . $folder->getPath();
        $object["size"] = FileSizeService::getFolderSize($path);

        if ($withParents) {
            $parents = $this->getStorageItemParentsObject($folder);
            $object["parents"] = array_reverse($parents);
        }

        if ($withContent) {
            $files = [];
            foreach ($folder->getFiles() as $f) {
                if (!$f->isInTrash() && $this->permissionService->hasItemPermission(
                        $this->security->getUser(),
                        Permission::READ,
                        $f
                    )) {
                    $files[] = $this->getFileObject($f);
                }
            }
            $object["files"] = $files;

            $folders = [];
            foreach ($folder->getFolders() as $f) {
                if (!$f->isInTrash() && $this->permissionService->hasItemPermission(
                        $this->security->getUser(),
                        Permission::READ,
                        $f
                    )) {
                    $folders[] = $this->getFolderObject($f, false, false);
                }
            }
            $object["folders"] = $folders;
        }
        return $object;
    }

    private function getStorageItemParentsObject(StorageItemInterface $item): array
    {
        $parents = [];
        $parent = $item->getFolder();

        if ($parent) {
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
        }

        return $parents;
    }
}
