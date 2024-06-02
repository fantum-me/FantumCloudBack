<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractPermissionManager;
use App\Repository\AccessControlRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: AccessControlRepository::class)]
class AccessControl extends AbstractPermissionManager
{
    #[ORM\ManyToOne(inversedBy: 'accessControls')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Role $role = null;

    #[ORM\ManyToOne(inversedBy: 'accessControls')]
    #[Ignore]
    private ?StorageItem $item = null;

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getItem(): ?StorageItem
    {
        return $this->item;
    }

    public function setItem(StorageItem $item): static
    {
        $this->item = $item;
        return $this;
    }
}
