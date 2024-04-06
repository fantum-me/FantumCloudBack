<?php

namespace App\Tests\Service;

use App\Entity\Role;
use App\Entity\Workspace;
use App\Repository\RoleRepository;
use App\Service\RolePositionService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class RolePositionServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager->method("flush")->willReturnSelf();
    }

    public function testSetExistingRolePosition(): void
    {
        $roles = $this->getRolesData();

        $roleRepository = $this->createMock(RoleRepository::class);
        $roleRepository->method("findBy")->willReturn($roles);

        $positionService = new RolePositionService($roleRepository, $this->entityManager);

        $targetRole = $roles[2];
        $defaultPosition = $targetRole->getPosition();
        $targetPosition = $defaultPosition - 1;
        $positionService->setRolePosition($targetRole, $targetPosition);

        $this->assertEquals($targetPosition, $targetRole->getPosition());
        $this->assertEquals($defaultPosition, $roles[1]->getPosition());
        $this->assertEquals(0, $roles[0]->getPosition());
    }

    public function testSetNewRolePosition(): void
    {
        $roles = $this->getRolesData();
        $role = new Role();

        $roleRepository = $this->createMock(RoleRepository::class);
        $roleRepository->method("findBy")->willReturn($roles);

        $positionService = new RolePositionService($roleRepository, $this->entityManager);

        $positionService->setRolePosition($role, 2);

        $this->assertEquals(2, $role->getPosition());
        $this->assertEquals(3, $roles[2]->getPosition());
    }

    public function testSetNewAloneRolePosition(): void
    {
        $role = new Role();

        $roleRepository = $this->createMock(RoleRepository::class);
        $roleRepository->method("findBy")->willReturn([]);

        $positionService = new RolePositionService($roleRepository, $this->entityManager);

        $positionService->setRolePosition($role, 5);

        $this->assertEquals(0, $role->getPosition());
    }

    private function getRolesData(): array
    {
        $data = [
            ["Default", 0],
            ["Mod", 1],
            ["Admin", 2],
        ];

        $roles = [];

        $workspace = new Workspace();
        foreach ($data as $d) {
            $role = new Role();
            $role->setWorkspace($workspace)->setName($d[0])->setPosition($d[1]);
            $roles[] = $role;
        }
        return $roles;
    }
}
