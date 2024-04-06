<?php

namespace App\Entity\Interface;

interface PermissionManagerInterface extends UidInterface
{
    public function can($permission): ?bool;
    public function setPermission($permission, ?bool $value): static;
}
