<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractPermissionManager;
use App\Entity\Interface\StorageItemInterface;
use App\Repository\AccessControlRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: AccessControlRepository::class)]
class AccessControl extends AbstractPermissionManager
{
    #[ORM\ManyToOne(inversedBy: 'accessControls')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Role $role = null;

    #[ORM\ManyToOne(inversedBy: 'accessControls')]
    #[Ignore]
    private ?File $file = null;

    #[ORM\ManyToOne(inversedBy: 'accessControls')]
    #[Ignore]
    private ?Folder $folder = null;

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;

        return $this;
    }

    #[Ignore]
    public function getItem(): StorageItemInterface
    {
        return $this->folder ?? $this->file;
    }

    public function setItem(StorageItemInterface $item): static
    {
        if ($item instanceof Folder) $this->folder = $item;
        elseif ($item instanceof File) $this->file = $item;
        return $this;
    }
}
