<?php

namespace App\Tests\Factory;

use App\Entity\Role;
use App\Entity\Workspace;
use App\Factory\RoleFactory;
use App\Security\Permission;
use App\Service\RolePositionService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoleFactoryTest extends TestCase
{
    private RoleFactory $roleFactory;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $rolePositionService = $this->createMock(RolePositionService::class);
        $rolePositionService->method("setRolePosition")->willReturnSelf();

        $this->roleFactory = new RoleFactory(
            $rolePositionService,
            $this->createMock(EntityManagerInterface::class),
            $this->validator
        );
    }

    public function testCreateValidRole(): void
    {
        foreach ([true, false] as $permissionValue) {
            $name = "Role";
            $workspace = new Workspace();
            $permissions = $this->getPermissionsArray($permissionValue);

            $role = $this->roleFactory->createRole($name, 0, $workspace, $permissions);

            $this->assertInstanceOf(Role::class, $role);
            $this->assertEquals($name, $role->getName());
            $this->assertEquals($workspace, $role->getWorkspace());

            foreach (Permission::PERMISSIONS as $permission) {
                $this->assertEquals($permissionValue, $role->can($permission));
            }
        }
    }

    public function testCreateInvalidRole(): void
    {
        $violation = new ConstraintViolation('Validation error message', null, [], null, null, null);
        $violationList = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violationList);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Validation error message');

        $this->roleFactory->createRole("role", 1, (new Workspace()), $this->getPermissionsArray());
    }

    private function getPermissionsArray(bool $permitted = false): array
    {
        $permissions = [];
        foreach (Permission::PERMISSIONS as $permission) {
            $permissions[$permission] = $permitted;
        }
        return $permissions;
    }
}
