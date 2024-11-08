<?php

namespace App\Tests\Unit\Domain\StorageItem\Service;

use App\Domain\AccessControl\AccessControl;
use App\Domain\Folder\Folder;
use App\Domain\Member\Member;
use App\Domain\Role\Role;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\StorageItem\StorageItemInterface;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Uid\Uuid;

class StorageItemPermissionServiceTest extends TestCase
{
    private WorkspacePermissionService $workspacePermissionService;
    private StorageItemPermissionService $service;

    protected function setUp(): void
    {
        $this->workspacePermissionService = $this->createMock(WorkspacePermissionService::class);
        $this->service = new StorageItemPermissionService($this->workspacePermissionService);
    }

    public static function booleanProvider(): array
    {
        return [[true], [false]];
    }

    public function testAssertPermissionWithValidPermission(): void
    {
        $user = $this->createMock(User::class);
        $resource = $this->createMock(StorageItemInterface::class);
        $workspace = $this->createMock(Workspace::class);
        $member = $this->createMock(Member::class);

        $resource->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $user->expects($this->once())
            ->method('getWorkspaceMember')
            ->with($workspace)
            ->willReturn($member);

        $this->workspacePermissionService->expects($this->once())
            ->method('assertAccess')
            ->with($user, $workspace);

        $member->method('getWorkspace')->willReturn($workspace);
        $workspace->method('getOwner')->willReturn($this->createMock(Member::class));
        $resource->method('getAccessControls')->willReturn(new ArrayCollection());
        $resource->method('getFolder')->willReturn(null);

        $this->workspacePermissionService->expects($this->once())
            ->method('hasWorkspacePermission')
            ->with($member, Permission::READ, $workspace)
            ->willReturn(true);

        $this->service->assertPermission($user, Permission::READ, $resource);

        $this->addToAssertionCount(1);
    }

    public function testAssertPermissionThrowsExceptionForInvalidPermission(): void
    {
        $user = $this->createMock(User::class);
        $resource = $this->createMock(StorageItemInterface::class);
        $workspace = $this->createMock(Workspace::class);
        $member = $this->createMock(Member::class);

        $resource->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $resource->expects($this->once())
            ->method('getId')
            ->willReturn(Uuid::v4());

        $user->expects($this->once())
            ->method('getWorkspaceMember')
            ->with($workspace)
            ->willReturn($member);

        $this->workspacePermissionService->expects($this->once())
            ->method('assertAccess')
            ->with($user, $workspace);

        // Mock the behavior of hasItemPermission
        $member->method('getWorkspace')->willReturn($workspace);
        $workspace->method('getOwner')->willReturn($this->createMock(Member::class));
        $resource->method('getAccessControls')->willReturn(new ArrayCollection());
        $resource->method('getFolder')->willReturn(null);

        $this->workspacePermissionService->expects($this->once())
            ->method('hasWorkspacePermission')
            ->with($member, Permission::WRITE, $workspace)
            ->willReturn(false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->service->assertPermission($user, Permission::WRITE, $resource);
    }

    public function testHasItemPermissionForWorkspaceOwner(): void
    {
        $member = $this->createMock(Member::class);
        $resource = $this->createMock(StorageItemInterface::class);
        $workspace = $this->createMock(Workspace::class);

        $member->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $workspace->expects($this->once())
            ->method('getOwner')
            ->willReturn($member);

        $this->assertTrue($this->service->hasItemPermission($member, Permission::MANAGE_MEMBERS, $resource));
    }

    /**
     * @dataProvider booleanProvider
     */
    public function testHasItemPermissionWithDirectPermission(bool $allowed): void
    {
        $member = $this->createMock(Member::class);
        $resource = $this->createMock(StorageItemInterface::class);
        $workspace = $this->createMock(Workspace::class);
        $accessControl = $this->createMock(AccessControl::class);
        $role = $this->createMock(Role::class);

        $anotherAccessControl = $this->createMock(AccessControl::class);
        $anotherRole = $this->createMock(Role::class);

        $anotherAccessControl->method('getRole')
            ->willReturn($anotherRole);

        $member->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $workspace->expects($this->once())
            ->method('getOwner')
            ->willReturn($this->createMock(Member::class));

        $resource->expects($this->once())
            ->method('getAccessControls')
            ->willReturn(new ArrayCollection([$accessControl, $anotherAccessControl]));

        $accessControl->method('getRole')
            ->willReturn($role);

        $member->method('getRoles')
            ->willReturn(new ArrayCollection([$role]));

        $accessControl->expects($this->once())
            ->method('can')
            ->with(Permission::READ)
            ->willReturn($allowed);

        $this->assertEquals($allowed, $this->service->hasItemPermission($member, Permission::READ, $resource));
    }

    public function testHasItemPermissionWithParentFolderPermission(): void
    {
        $member = $this->createMock(Member::class);
        $resource = $this->createMock(StorageItemInterface::class);
        $workspace = $this->createMock(Workspace::class);
        $parentFolder = $this->createMock(Folder::class);

        $member->expects($this->exactly(2))
            ->method('getWorkspace')
            ->willReturn($workspace);

        $workspace->expects($this->exactly(2))
            ->method('getOwner')
            ->willReturn($this->createMock(Member::class));

        $resource->expects($this->once())
            ->method('getAccessControls')
            ->willReturn(new ArrayCollection());

        $resource->expects($this->once())
            ->method('getFolder')
            ->willReturn($parentFolder);

        $parentFolder->method('getAccessControls')->willReturn(new ArrayCollection());
        $parentFolder->method('getFolder')->willReturn(null);

        $this->workspacePermissionService->expects($this->once())
            ->method('hasWorkspacePermission')
            ->with($member, Permission::READ, $workspace)
            ->willReturn(true);

        $this->assertTrue($this->service->hasItemPermission($member, Permission::READ, $resource));
    }

    public function testHasItemPermissionFallbackToWorkspacePermission(): void
    {
        $member = $this->createMock(Member::class);
        $resource = $this->createMock(StorageItemInterface::class);
        $workspace = $this->createMock(Workspace::class);

        $member->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $workspace->expects($this->once())
            ->method('getOwner')
            ->willReturn($this->createMock(Member::class));

        $resource->expects($this->once())
            ->method('getAccessControls')
            ->willReturn(new ArrayCollection());

        $resource->expects($this->once())
            ->method('getFolder')
            ->willReturn(null);

        $this->workspacePermissionService->expects($this->once())
            ->method('hasWorkspacePermission')
            ->with($member, Permission::READ, $workspace)
            ->willReturn(true);

        $this->assertTrue($this->service->hasItemPermission($member, Permission::READ, $resource));
    }

    public function testHasItemPermissionWithDeniedAccessControl(): void
    {
        $member = $this->createMock(Member::class);
        $resource = $this->createMock(StorageItemInterface::class);
        $accessControl = $this->createMock(AccessControl::class);
        $role = $this->createMock(Role::class);
        $workspace = $this->createMock(Workspace::class);

        $member->method('getWorkspace')->willReturn($workspace);
        $workspace->method('getOwner')->willReturn($this->createMock(Member::class));

        $resource->expects($this->once())
            ->method('getAccessControls')
            ->willReturn(new ArrayCollection([$accessControl]));

        $accessControl->expects($this->once())
            ->method('getRole')
            ->willReturn($role);

        $member->expects($this->once())
            ->method('getRoles')
            ->willReturn(new ArrayCollection([$role]));

        $accessControl->expects($this->once())
            ->method('can')
            ->with(Permission::READ)
            ->willReturn(false);

        // Since there is a permission denial, the method should return false.
        $this->assertFalse($this->service->hasItemPermission($member, Permission::READ, $resource));
    }
}
