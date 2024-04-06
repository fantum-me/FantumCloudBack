<?php

namespace App\Tests\Entity;

use App\Entity\Abstract\AbstractPermissionManager;
use App\Security\Permission;
use PHPUnit\Framework\TestCase;

class PermissionManagerTest extends TestCase
{
    public function testPermissionManager(): void
    {
        $permissionManager = new class extends AbstractPermissionManager {
        };

        foreach ([true, false, null] as $value) {
            foreach (Permission::PERMISSIONS as $permission) {
                $permissionManager->setPermission($permission, $value);
                $this->assertEquals($value, $permissionManager->can($permission));
            }
        }
    }
}
