<?php

namespace App\Entity\Abstract;

use App\Security\Permission;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedPath;

abstract class AbstractRolePermissionManager extends AbstractPermissionManager
{
    #[ORM\Column(nullable: true)]
    #[Groups(["default"])]
    #[SerializedPath("[permissions][manage_members]")]
    public ?bool $canManageMembers = null;

    public function can($permission): ?bool
    {
        if ($permission === Permission::MANAGE_MEMBERS) {
            return $this->canManageMembers;
        }
        return parent::can($permission);
    }

    public function setPermission($permission, ?bool $value): static
    {
        if ($permission === Permission::MANAGE_MEMBERS) {
            $this->canManageMembers = $value;
        } else {
            parent::setPermission($permission, $value);
        }
        return $this;
    }
}
