<?php

namespace App\Entity\Abstract;

use App\Entity\Interface\PermissionManagerInterface;
use App\Security\Permission;
use Doctrine\ORM\Mapping as ORM;

abstract class AbstractPermissionManager extends AbstractUid implements PermissionManagerInterface
{
    #[ORM\Column(nullable: true)]
    protected ?bool $canRead = null;

    #[ORM\Column(nullable: true)]
    protected ?bool $canWrite = null;

    #[ORM\Column(nullable: true)]
    protected ?bool $canTrash = null;

    #[ORM\Column(nullable: true)]
    protected ?bool $canDelete = null;

    #[ORM\Column(nullable: true)]
    protected ?bool $canEditPermissions = null;

    public function can($permission): ?bool
    {
        if ($permission === Permission::READ) return $this->canRead;
        elseif ($permission === Permission::WRITE) return $this->canWrite;
        elseif ($permission === Permission::TRASH) return $this->canTrash;
        elseif ($permission === Permission::DELETE) return $this->canDelete;
        elseif ($permission === Permission::EDIT_PERMISSIONS) return $this->canEditPermissions;
        return null;
    }

    public function setPermission($permission, ?bool $value): static
    {
        if ($permission === Permission::READ) $this->canRead = $value;
        elseif ($permission === Permission::WRITE) $this->canWrite = $value;
        elseif ($permission === Permission::TRASH) $this->canTrash = $value;
        elseif ($permission === Permission::DELETE) $this->canDelete = $value;
        elseif ($permission === Permission::EDIT_PERMISSIONS) $this->canEditPermissions = $value;

        return $this;
    }
}
