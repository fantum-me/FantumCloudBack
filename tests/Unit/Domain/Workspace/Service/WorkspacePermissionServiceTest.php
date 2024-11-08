<?php

namespace App\Tests\Unit\Domain\Workspace\Service;

use App\Domain\Member\Member;
use App\Domain\Role\Role;
use App\Domain\User\User;
use App\Domain\Workspace\Service\WorkspacePermissionService;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class WorkspacePermissionServiceTest extends TestCase
{
    private WorkspacePermissionService $service;

    protected function setUp(): void
    {
        $this->service = new WorkspacePermissionService();
    }

    public function testAssertPermissionWithValidPermission(): void
    {
        $user = $this->createMock(User::class);
        $workspace = $this->createMock(Workspace::class);
        $member = $this->createMock(Member::class);

        $user->expects($this->once())
            ->method('isInWorkspace')
            ->with($workspace)
            ->willReturn(true);

        $user->expects($this->once())
            ->method('getWorkspaceMember')
            ->with($workspace)
            ->willReturn($member);

        $workspace->method('getOwner')->willReturn($member);

        $this->service->assertPermission($user, Permission::READ, $workspace);

        $this->addToAssertionCount(1);
    }

    public function testAssertPermissionThrowsExceptionForInvalidPermission(): void
    {
        $user = $this->createMock(User::class);
        $workspace = $this->createMock(Workspace::class);
        $member = $this->createMock(Member::class);

        $user->expects($this->once())
            ->method('isInWorkspace')
            ->with($workspace)
            ->willReturn(true);

        $user->expects($this->once())
            ->method('getWorkspaceMember')
            ->with($workspace)
            ->willReturn($member);

        $workspace->method('getOwner')->willReturn($this->createMock(Member::class));

        $this->expectException(AccessDeniedHttpException::class);

        $this->service->assertPermission($user, Permission::READ, $workspace);
    }

    public function testAssertAccessWithValidAccess(): void
    {
        $user = $this->createMock(User::class);
        $workspace = $this->createMock(Workspace::class);

        $user->expects($this->once())
            ->method('isInWorkspace')
            ->with($workspace)
            ->willReturn(true);

        $this->service->assertAccess($user, $workspace);

        $this->addToAssertionCount(1);
    }

    public function testAssertAccessThrowsExceptionForInvalidAccess(): void
    {
        $user = $this->createMock(User::class);
        $workspace = $this->createMock(Workspace::class);

        $user->expects($this->once())
            ->method('isInWorkspace')
            ->with($workspace)
            ->willReturn(false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->service->assertAccess($user, $workspace);
    }

    public function testHasWorkspacePermissionForOwner(): void
    {
        $member = $this->createMock(Member::class);
        $workspace = $this->createMock(Workspace::class);

        $workspace->method('getOwner')->willReturn($member);

        $result = $this->service->hasWorkspacePermission($member, Permission::MANAGE_MEMBERS, $workspace);

        $this->assertTrue($result);
    }

    public function testHasWorkspacePermissionForMemberWithPermission(): void
    {
        $member = $this->createMock(Member::class);
        $workspace = $this->createMock(Workspace::class);
        $role = $this->createMock(Role::class);

        $workspace->method('getOwner')->willReturn($this->createMock(Member::class));
        $member->method("getRoles")->willReturn(new ArrayCollection([$role]));

        $role->expects($this->once())
            ->method('can')
            ->with(Permission::READ)
            ->willReturn(true);

        $result = $this->service->hasWorkspacePermission($member, Permission::READ, $workspace);

        $this->assertTrue($result);
    }

    public function testHasWorkspacePermissionForMemberWithoutPermission(): void
    {
        $member = $this->createMock(Member::class);
        $workspace = $this->createMock(Workspace::class);
        $role = $this->createMock(Role::class);

        $workspace->method('getOwner')->willReturn($this->createMock(Member::class));
        $member->method("getRoles")->willReturn(new ArrayCollection([$role]));

        $role->expects($this->once())
            ->method('can')
            ->with(Permission::READ)
            ->willReturn(false);

        $result = $this->service->hasWorkspacePermission($member, Permission::READ, $workspace);

        $this->assertFalse($result);
    }

    public function testHasWorkspacePermissionForNullMember(): void
    {
        $workspace = $this->createMock(Workspace::class);

        $result = $this->service->hasWorkspacePermission(null, Permission::READ, $workspace);

        $this->assertFalse($result);
    }
}
