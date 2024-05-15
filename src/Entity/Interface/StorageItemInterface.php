<?php

namespace App\Entity\Interface;

use App\Entity\AccessControl;
use App\Entity\Folder;
use App\Entity\Workspace;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;

interface StorageItemInterface extends UidInterface
{
    public function getFolder(): ?Folder;

    public function setFolder(?Folder $folder): static;

    public function getWorkspace(): ?Workspace;

    public function setWorkspace(?Workspace $workspace): static;

    public function getName(): ?string;

    public function setName(string $name): static;

    public function getFullSystemFileName(): ?string;

    public function getSystemFileName(): ?string;

    public function getPath(): ?string;

    public function isInTrash(): ?bool;

    public function setInTrash(bool $inTrash): static;

    public function getTrashedAt(): ?DateTimeInterface;

    public function updateVersion($withParent = true): void;

    public function getVersion(): int;

    public function getAccessControls(): ?Collection;

    public function addAccessControl(AccessControl $accessControl): static;

    public function removeAccessControl(AccessControl $accessControl): static;
}
